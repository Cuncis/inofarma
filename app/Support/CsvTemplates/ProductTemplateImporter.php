<?php

namespace App\Support\CsvTemplates;

use App\Models\Product;
use App\Models\Supplier;
use App\Support\CategoryResolver;
use App\Support\ProductImageFetcher;
use App\Support\Slug;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Creates or updates products from `produk.csv` (see {@see ProductTemplate}).
 *
 * `sku` is the idempotency key: importing the same file twice updates rows
 * instead of duplicating them. A column that is absent from the file leaves
 * that field alone; a column that is present but empty clears the field when
 * it is optional (and leaves it alone for `storage`, `status` and
 * `requires_prescription`, which always hold a value).
 *
 * Only product data is touched. Stock comes in through
 * {@see StockTemplateImporter}.
 *
 * Bebas terbatas products must show a warning label by law, so one imported
 * without warning text is saved as `nonaktif` until an admin fills it in.
 */
class ProductTemplateImporter
{
    private const TEXT_COLUMNS = [
        'nie_bpom', 'composition', 'manufacturer', 'blurb', 'description',
        'indication', 'dosage', 'side_effects', 'warning',
    ];

    private const MONEY_COLUMNS = ['old_price', 'cost_price'];

    private const TRUE_VALUES = ['1', 'ya', 'yes', 'true'];

    private const FALSE_VALUES = ['0', 'tidak', 'no', 'false'];

    private CategoryResolver $categories;

    private ProductImageFetcher $images;

    public function __construct()
    {
        $this->categories = new CategoryResolver;
        $this->images = new ProductImageFetcher;
    }

    /**
     * @return array{kind: string, created: int, updated: int, warnedInactive: int, imagesFailed: list<string>, failed: list<array{row: int, message: string}>}
     *
     * @throws RuntimeException when the file is empty or lacks a required column
     */
    public function import(string $path): array
    {
        ['header' => $header, 'rows' => $rows] = CsvTable::read($path);
        CsvTable::assertColumns($header, ProductTemplate::REQUIRED);

        $summary = ['kind' => 'produk', 'created' => 0, 'updated' => 0, 'warnedInactive' => 0, 'imagesFailed' => [], 'failed' => []];

        foreach ($rows as ['line' => $line, 'cells' => $cells]) {
            try {
                $this->importRow($cells, $header, $summary);
            } catch (\Throwable $e) {
                $summary['failed'][] = ['row' => $line, 'message' => $e->getMessage()];
            }
        }

        $this->finishImages($summary);

        return $summary;
    }

    /**
     * Downloads the images queued by {@see importRow()}. {@see import()} calls
     * this itself; a caller driving `importRow()` directly calls it at the end.
     *
     * @param  array{imagesFailed: list<string>}  $summary
     */
    public function finishImages(array &$summary): void
    {
        $this->images->fetch($summary);
    }

    /**
     * Saves one product row.
     *
     * With `$partial` (the combined product + stock file), a product that
     * already exists needs only `sku` plus whichever columns should change; a
     * new product still needs every required column.
     *
     * @param  array<string, string>  $cells
     * @param  list<string>  $header  the columns `$cells` really carries; others are left alone
     * @param  array{created: int, updated: int, warnedInactive: int}  $summary
     */
    public function importRow(array $cells, array $header, array &$summary, bool $partial = false): void
    {
        foreach (['drug_class', 'storage', 'status', 'requires_prescription'] as $column) {
            if (isset($cells[$column])) {
                $cells[$column] = mb_strtolower($cells[$column]);
            }
        }

        $product = Product::withTrashed()->where('sku', $cells['sku'] ?? '')->first();
        $isNew = $product === null;

        $this->validate($cells, enforceRequired: ! $partial || $isNew);

        $product ??= new Product(['sku' => $cells['sku']]);

        if ($product->trashed()) {
            $product->restore();
        }

        $has = fn (string $column) => in_array($column, $header, true);

        $attributes = [];

        foreach (['name', 'unit', 'drug_class'] as $column) {
            if ($has($column)) {
                $attributes[$column] = $cells[$column];
            }
        }

        if ($has('category')) {
            $attributes['category_id'] = $this->categories->id($cells['category']);
        }

        foreach (['price', 'weight_grams'] as $column) {
            if ($has($column)) {
                $attributes[$column] = (int) $cells[$column];
            }
        }

        foreach (self::TEXT_COLUMNS as $column) {
            if ($has($column)) {
                $attributes[$column] = $cells[$column] !== '' ? $cells[$column] : null;
            }
        }

        foreach (self::MONEY_COLUMNS as $column) {
            if ($has($column)) {
                $attributes[$column] = $cells[$column] !== '' ? (int) $cells[$column] : null;
            }
        }

        if ($has('max_qty_per_order')) {
            $maxQty = (int) $cells['max_qty_per_order'];
            $attributes['max_qty_per_order'] = $maxQty > 0 ? $maxQty : null;
        }

        if ($has('supplier')) {
            $attributes['supplier_id'] = $cells['supplier'] !== '' ? $this->supplierId($cells['supplier']) : null;
        }

        foreach (['storage', 'status'] as $column) {
            if ($has($column) && $cells[$column] !== '') {
                $attributes[$column] = $cells[$column];
            }
        }

        if ($has('requires_prescription') && $cells['requires_prescription'] !== '') {
            $attributes['requires_prescription'] = in_array($cells['requires_prescription'], self::TRUE_VALUES, true);
        }

        if ($isNew || $has('slug') || $has('name')) {
            $attributes['slug'] = $this->slugFor($product, $cells, $has('slug'));
        }

        $product->fill($attributes);

        $touchesWarningRules = ! $partial || $isNew || $has('drug_class') || $has('warning') || $has('status');

        if ($touchesWarningRules && $product->needs_warning_label && blank($product->warning)) {
            $product->status = 'nonaktif';
            $summary['warnedInactive']++;
        }

        $product->save();

        $summary[$isNew ? 'created' : 'updated']++;

        $urls = $has('image_url') ? $this->imageUrls($cells['image_url']) : [];

        if ($urls !== [] && ! $product->images()->exists()) {
            $this->images->queue($product, $urls);
        }
    }

    /**
     * @param  array<string, string>  $cells
     */
    private function validate(array $cells, bool $enforceRequired = true): void
    {
        $digits = 'regex:/^\d+$/';

        $rules = [
            'sku' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', $digits],
            'old_price' => ['nullable', $digits],
            'cost_price' => ['nullable', $digits],
            'drug_class' => ['required', 'in:'.implode(',', ProductTemplate::DRUG_CLASSES)],
            'requires_prescription' => ['nullable', 'in:'.implode(',', [...self::TRUE_VALUES, ...self::FALSE_VALUES])],
            'nie_bpom' => ['nullable', 'max:40'],
            'composition' => ['nullable', 'max:255'],
            'manufacturer' => ['nullable', 'max:255'],
            'blurb' => ['nullable', 'max:500'],
            'storage' => ['nullable', 'in:'.implode(',', ProductTemplate::STORAGES)],
            'weight_grams' => ['required', $digits],
            'max_qty_per_order' => ['nullable', $digits, 'max:65535'],
            'status' => ['nullable', 'in:'.implode(',', ProductTemplate::STATUSES)],
        ];

        if (! $enforceRequired) {
            foreach ($rules as $column => $columnRules) {
                $rules[$column] = array_map(fn ($rule) => $rule === 'required' && $column !== 'sku' ? 'sometimes' : $rule, $columnRules);
            }
        }

        $validator = Validator::make($cells, $rules, [
            'regex' => 'Kolom :attribute harus berupa bilangan bulat tanpa titik, koma, atau "Rp" (contoh: 15000).',
            'slug.regex' => 'Kolom slug hanya boleh huruf kecil, angka, dan tanda hubung.',
            'in' => 'Kolom :attribute berisi nilai yang tidak dikenal.',
            'required' => 'Kolom :attribute wajib diisi.',
            'max' => 'Kolom :attribute terlalu panjang atau terlalu besar.',
        ]);

        if ($validator->fails()) {
            throw new RuntimeException($validator->errors()->first());
        }
    }

    private function supplierId(string $name): int
    {
        $supplier = Supplier::where('name', $name)->first();

        if ($supplier === null) {
            throw new RuntimeException("Pemasok \"{$name}\" tidak ditemukan. Tambahkan dulu di menu Pemasok.");
        }

        return $supplier->id;
    }

    /**
     * New products get the given slug or one made from the name. Existing
     * products keep their slug (it is in storefront URLs) unless the file
     * gives a different one on purpose.
     *
     * @param  array<string, string>  $cells
     */
    private function slugFor(Product $product, array $cells, bool $hasSlugColumn): string
    {
        $given = $hasSlugColumn ? $cells['slug'] : '';

        if ($given === '') {
            return $product->exists ? $product->slug : Slug::unique(Product::withTrashed(), $cells['name']);
        }

        $taken = Product::withTrashed()
            ->where('slug', $given)
            ->when($product->exists, fn ($query) => $query->whereKeyNot($product->getKey()))
            ->exists();

        if ($taken) {
            throw new RuntimeException("Slug \"{$given}\" sudah dipakai produk lain.");
        }

        return $given;
    }

    /**
     * @return list<string>
     */
    private function imageUrls(string $raw): array
    {
        $urls = array_values(array_filter(array_map('trim', explode('|', $raw))));

        foreach ($urls as $url) {
            if (! preg_match('#^https?://#i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
                throw new RuntimeException("Alamat gambar tidak valid: {$url}");
            }
        }

        return $urls;
    }
}
