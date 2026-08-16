<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use App\Support\AdminOptions;
use App\Support\CodeSequence;
use App\Support\Presenters\BranchPresenter;
use App\Support\Slug;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Branch CRUD for the admin — the same pattern as Product/Category.
 *
 * A branch with stock on its shelves or orders in its history cannot be
 * deleted; set its status to "Tutup Permanen" instead. Deleting it would
 * orphan real inventory and real financial records, not just a catalogue row.
 */
class BranchController extends Controller
{
    /**
     * A new branch needs some hours to be anything but permanently "closed" —
     * the form doesn't edit the weekly schedule yet (a known gap; a proper
     * per-day editor belongs with the rest of the branch console work). Admins
     * can still fix these directly until that screen exists.
     *
     * @var array<string, array{open: string, close: string}>
     */
    private const DEFAULT_HOURS = [
        'senin' => ['open' => '08:00', 'close' => '21:00'],
        'selasa' => ['open' => '08:00', 'close' => '21:00'],
        'rabu' => ['open' => '08:00', 'close' => '21:00'],
        'kamis' => ['open' => '08:00', 'close' => '21:00'],
        'jumat' => ['open' => '08:00', 'close' => '21:00'],
        'sabtu' => ['open' => '08:00', 'close' => '21:00'],
        'minggu' => ['open' => '09:00', 'close' => '20:00'],
    ];

    public function index(): Response
    {
        $branches = Branch::query()
            ->withCount('stocks', 'staff')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/BranchList', [
            'branches' => BranchPresenter::collection($branches),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/BranchAdd', [
            'statuses' => AdminOptions::labels(AdminOptions::BRANCH_STATUSES),
        ]);
    }

    public function store(BranchRequest $request): RedirectResponse
    {
        $branch = Branch::create($this->attributes($request->validated()) + [
            'code' => CodeSequence::next(Branch::withTrashed(), 'code', 'CB-'),
            'operating_hours' => self::DEFAULT_HOURS,
        ]);

        return redirect()
            ->route('admin.cabang.index')
            ->with('success', "Cabang \"{$branch->name}\" berhasil ditambahkan.");
    }

    public function show(string $branch): Response
    {
        $record = $this->find($branch, ['stocks.product', 'orders']);

        return Inertia::render('Admin/BranchDetail', [
            'branch' => BranchPresenter::toArray($record),
        ]);
    }

    public function edit(string $branch): Response
    {
        return Inertia::render('Admin/BranchEdit', [
            'branch' => BranchPresenter::toArray($this->find($branch)),
            'statuses' => AdminOptions::labels(AdminOptions::BRANCH_STATUSES),
        ]);
    }

    public function update(BranchRequest $request, string $branch): RedirectResponse
    {
        $record = $this->find($branch);
        $record->update($this->attributes($request->validated(), $record));

        return redirect()
            ->route('admin.cabang.index')
            ->with('success', "Cabang \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $branch): RedirectResponse
    {
        $record = $this->find($branch);
        $stockCount = $record->stocks()->where('quantity', '>', 0)->count();
        $orderCount = $record->orders()->count();

        if ($stockCount > 0 || $orderCount > 0) {
            return redirect()
                ->route('admin.cabang.index')
                ->with('error', "\"{$record->name}\" masih punya stok atau riwayat pesanan dan tidak bisa dihapus. Ubah statusnya menjadi Tutup Permanen.");
        }

        $name = $record->name;
        $record->delete();

        return redirect()
            ->route('admin.cabang.index')
            ->with('success', "Cabang \"{$name}\" berhasil dihapus.");
    }

    public function reset(): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        (new DemoDataSeeder)->run();

        return redirect()
            ->route('admin.cabang.index')
            ->with('success', 'Data cabang dikembalikan ke data awal.');
    }

    /**
     * @param  list<string>  $with
     */
    private function find(string $code, array $with = []): Branch
    {
        return Branch::query()
            ->with($with)
            ->where('code', $code)
            ->firstOr(fn () => abort(404, 'Cabang tidak ditemukan.'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, ?Branch $editing = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => Slug::unique(Branch::withTrashed(), $data['name'], 'slug', $editing?->id),
            'address_line' => $data['addressLine'],
            'kelurahan' => $data['kelurahan'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kota' => $data['kota'],
            'provinsi' => $data['provinsi'],
            'postal_code' => $data['postalCode'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'maps_url' => isset($data['latitude'], $data['longitude'])
                ? "https://www.google.com/maps?q={$data['latitude']},{$data['longitude']}"
                : null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'sia_number' => $data['siaNumber'] ?? null,
            'apj_name' => $data['apjName'] ?? null,
            'apj_sipa_number' => $data['apjSipaNumber'] ?? null,
            'supports_delivery' => $data['supportsDelivery'],
            'supports_pickup' => $data['supportsPickup'],
            'delivery_radius_km' => $data['deliveryRadiusKm'],
            'status' => AdminOptions::toValue(AdminOptions::BRANCH_STATUSES, $data['status']),
        ];
    }
}
