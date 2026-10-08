<?php

namespace App\Support\CsvTemplates;

use App\Models\Branch;
use App\Models\Product;
use App\Support\Inventory\StockReplacer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Replaces batch stock from `stok.csv` (see {@see StockTemplate}).
 *
 * The rows for one product at one branch are the complete list of its batches:
 * quantities are set to the file's numbers, and batches the branch holds that
 * are not in the file go to zero ({@see StockReplacer}). Products and branches
 * that do not appear in the file are left alone.
 *
 * Because a missing batch is zeroed, a group is applied all or nothing: if any
 * row of a product + branch is invalid, none of that group is applied, so a
 * typo can never wipe out a batch that was simply on the broken row.
 */
class StockTemplateImporter
{
    private const DATE_FORMATS = ['!Y-m-d', '!d/m/Y'];

    private const TRUE_VALUES = ['1', 'ya', 'yes', 'true'];

    private const FALSE_VALUES = ['0', 'tidak', 'no', 'false'];

    /** @var array<string, Branch|null> */
    private array $branches = [];

    /** @var array<string, Product|null> */
    private array $products = [];

    /**
     * @param  ?int  $restrictToBranchId  a branch-confined staff member can only import their own branch
     */
    public function __construct(
        private readonly ?int $restrictToBranchId = null,
        private readonly ?int $userId = null,
        private readonly StockReplacer $replacer = new StockReplacer,
    ) {}

    /**
     * @return array{kind: string, groups: int, batches: int, zeroed: int, failed: list<array{row: int, message: string}>}
     *
     * @throws RuntimeException when the file is empty or lacks a required column
     */
    public function import(string $path, string $fileName = 'stok.csv'): array
    {
        ['header' => $header, 'rows' => $rows] = CsvTable::read($path);
        CsvTable::assertColumns($header, StockTemplate::REQUIRED);

        $summary = ['kind' => 'stok', 'groups' => 0, 'batches' => 0, 'zeroed' => 0, 'failed' => []];

        /** @var array<string, array{firstLine: int, branch: ?Branch, product: ?Product, entries: list<array<string, mixed>>, settings: array<string, mixed>, seen: array<string, true>, broken: bool}> $groups */
        $groups = [];

        foreach ($rows as ['line' => $line, 'cells' => $cells]) {
            $groupKey = mb_strtolower($cells['branch_code'].'|'.$cells['sku']);
            $group = &$groups[$groupKey];
            $group ??= ['firstLine' => $line, 'branch' => null, 'product' => null, 'entries' => [], 'settings' => [], 'seen' => [], 'broken' => false];

            try {
                $this->collectRow($cells, $header, $group);
            } catch (RuntimeException $e) {
                $group['broken'] = true;
                $summary['failed'][] = ['row' => $line, 'message' => $e->getMessage()];
            }

            unset($group);
        }

        $note = "Impor stok CSV ({$fileName})";

        foreach ($groups as $group) {
            if ($group['broken']) {
                $summary['failed'][] = [
                    'row' => $group['firstLine'],
                    'message' => 'Stok produk ini di cabang tersebut tidak diubah karena ada baris yang salah.',
                ];

                continue;
            }

            try {
                $result = $this->replacer->replace(
                    $group['branch'], $group['product'], $group['entries'], $group['settings'], $this->userId, $note,
                );
            } catch (\Throwable $e) {
                $summary['failed'][] = ['row' => $group['firstLine'], 'message' => $e->getMessage()];

                continue;
            }

            $summary['groups']++;
            $summary['batches'] += $result['batches'];
            $summary['zeroed'] += $result['zeroed'];
        }

        return $summary;
    }

    /**
     * @param  array<string, string>  $cells
     * @param  list<string>  $header
     * @param  array<string, mixed>  $group
     */
    private function collectRow(array $cells, array $header, array &$group): void
    {
        $validator = Validator::make($cells, [
            'sku' => ['required'],
            'branch_code' => ['required'],
            'batch_number' => ['required', 'max:60'],
            'expires_at' => ['required'],
            'quantity' => ['required', 'regex:/^\d+$/'],
            'batch_cost_price' => ['nullable', 'regex:/^\d+$/'],
            'reorder_point' => ['nullable', 'regex:/^\d+$/'],
            'price_override' => ['nullable', 'regex:/^\d+$/'],
        ], [
            'required' => 'Kolom :attribute wajib diisi.',
            'regex' => 'Kolom :attribute harus berupa bilangan bulat tanpa titik atau koma.',
            'max' => 'Kolom :attribute terlalu panjang.',
        ]);

        if ($validator->fails()) {
            throw new RuntimeException($validator->errors()->first());
        }

        $branch = $this->branch($cells['branch_code']);
        $product = $this->product($cells['sku']);

        $group['branch'] = $branch;
        $group['product'] = $product;

        $batchKey = mb_strtolower($cells['batch_number']);

        if (isset($group['seen'][$batchKey])) {
            throw new RuntimeException("Batch {$cells['batch_number']} muncul lebih dari sekali untuk produk dan cabang yang sama.");
        }

        $entry = [
            'batch_number' => $cells['batch_number'],
            'expires_at' => $this->date($cells['expires_at'], 'expires_at'),
            'quantity' => (int) $cells['quantity'],
        ];

        if (in_array('batch_cost_price', $header, true) && $cells['batch_cost_price'] !== '') {
            $entry['cost_price'] = (int) $cells['batch_cost_price'];
        }

        if (in_array('received_at', $header, true) && $cells['received_at'] !== '') {
            $entry['received_at'] = $this->date($cells['received_at'], 'received_at');
        }

        $this->collectSettings($cells, $header, $group['settings']);

        $group['seen'][$batchKey] = true;
        $group['entries'][] = $entry;
    }

    /**
     * Branch-level settings can sit on any row of the group; the first
     * non-empty value for each wins. Empty means "leave as it is".
     *
     * @param  array<string, string>  $cells
     * @param  list<string>  $header
     * @param  array<string, mixed>  $settings
     */
    private function collectSettings(array $cells, array $header, array &$settings): void
    {
        foreach (['reorder_point', 'price_override'] as $column) {
            if (in_array($column, $header, true) && $cells[$column] !== '' && ! isset($settings[$column])) {
                $settings[$column] = (int) $cells[$column];
            }
        }

        if (in_array('is_listed', $header, true) && $cells['is_listed'] !== '' && ! isset($settings['is_listed'])) {
            $value = mb_strtolower($cells['is_listed']);

            if (! in_array($value, [...self::TRUE_VALUES, ...self::FALSE_VALUES], true)) {
                throw new RuntimeException('Kolom is_listed harus 1 atau 0.');
            }

            $settings['is_listed'] = in_array($value, self::TRUE_VALUES, true);
        }
    }

    private function branch(string $code): Branch
    {
        $key = mb_strtolower($code);

        $this->branches[$key] ??= Branch::where('code', $code)->first();

        $branch = $this->branches[$key];

        if ($branch === null) {
            throw new RuntimeException("Cabang dengan kode \"{$code}\" tidak ditemukan.");
        }

        if ($this->restrictToBranchId !== null && $branch->id !== $this->restrictToBranchId) {
            throw new RuntimeException("Anda tidak punya akses ke cabang \"{$code}\".");
        }

        return $branch;
    }

    private function product(string $sku): Product
    {
        $key = mb_strtolower($sku);

        $this->products[$key] ??= Product::where('sku', $sku)->first();

        return $this->products[$key]
            ?? throw new RuntimeException("Produk dengan SKU \"{$sku}\" tidak ditemukan. Import produk lebih dulu.");
    }

    private function date(string $value, string $column): string
    {
        foreach (self::DATE_FORMATS as $format) {
            if (Carbon::canBeCreatedFromFormat($value, $format)) {
                return Carbon::createFromFormat($format, $value)->toDateString();
            }
        }

        throw new RuntimeException("Kolom {$column} harus berformat YYYY-MM-DD (contoh: 2027-06-30).");
    }
}
