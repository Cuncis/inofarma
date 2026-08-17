<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeRequest;
use App\Models\Attribute;
use App\Support\AdminOptions;
use App\Support\Presenters\AttributePresenter;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Attribute CRUD for the admin. Not referenced by any other table, so delete
 * has nothing to refuse — unlike Category/Supplier, there is no "still in
 * use" state to check.
 */
class AttributeController extends Controller
{
    public function index(): Response
    {
        $attributes = Attribute::orderBy('name')->get();

        return Inertia::render('Admin/AttributeList', [
            'attributes' => AttributePresenter::collection($attributes),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AttributeAdd', [
            'types' => AdminOptions::labels(AdminOptions::ATTRIBUTE_TYPES),
        ]);
    }

    public function store(AttributeRequest $request): RedirectResponse
    {
        $attribute = Attribute::create($this->attributes($request->validated()));

        return redirect()
            ->route('admin.atribut.index')
            ->with('success', "Atribut \"{$attribute->name}\" berhasil ditambahkan.");
    }

    public function edit(string $attribute): Response
    {
        return Inertia::render('Admin/AttributeEdit', [
            'attribute' => AttributePresenter::toArray($this->find($attribute)),
            'types' => AdminOptions::labels(AdminOptions::ATTRIBUTE_TYPES),
        ]);
    }

    public function update(AttributeRequest $request, string $attribute): RedirectResponse
    {
        $record = $this->find($attribute);
        $record->update($this->attributes($request->validated(), $record));

        return redirect()
            ->route('admin.atribut.index')
            ->with('success', "Atribut \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $attribute): RedirectResponse
    {
        $record = $this->find($attribute);
        $name = $record->name;
        $record->delete();

        return redirect()
            ->route('admin.atribut.index')
            ->with('success', "Atribut \"{$name}\" berhasil dihapus.");
    }

    private function find(string $slug): Attribute
    {
        return Attribute::where('slug', $slug)->firstOr(fn () => abort(404, 'Atribut tidak ditemukan.'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, ?Attribute $editing = null): array
    {
        $type = AdminOptions::toValue(AdminOptions::ATTRIBUTE_TYPES, $data['type']);

        return [
            'name' => $data['name'],
            'slug' => Slug::unique(Attribute::withTrashed(), $data['name'], 'slug', $editing?->id),
            'type' => $type,
            'values' => $type === 'pilihan' ? array_values($data['values'] ?? []) : null,
        ];
    }
}
