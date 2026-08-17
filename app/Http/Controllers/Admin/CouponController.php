<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponRequest;
use App\Models\Branch;
use App\Models\Coupon;
use App\Support\AdminOptions;
use App\Support\Presenters\CouponPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Coupon CRUD for the admin.
 *
 * Redemption (applying a code at checkout, one-per-customer) is Fase 5's job
 * — this only manages a coupon's own rules and its branch scope.
 */
class CouponController extends Controller
{
    public function index(): Response
    {
        $coupons = Coupon::with('branches')->orderBy('code')->get();

        return Inertia::render('Admin/CouponList', [
            'coupons' => CouponPresenter::collection($coupons),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/CouponAdd', $this->formOptions());
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $coupon = Coupon::create($this->attributes($data));
        $coupon->branches()->sync($this->branchIds($data));

        return redirect()
            ->route('admin.kupon.index')
            ->with('success', "Kupon \"{$coupon->code}\" berhasil ditambahkan.");
    }

    public function edit(string $coupon): Response
    {
        $record = $this->find($coupon);

        return Inertia::render('Admin/CouponEdit', [
            ...$this->formOptions(),
            'coupon' => CouponPresenter::toArray($record),
        ]);
    }

    public function update(CouponRequest $request, string $coupon): RedirectResponse
    {
        $record = $this->find($coupon);
        $data = $request->validated();

        $record->update($this->attributes($data));
        $record->branches()->sync($this->branchIds($data));

        return redirect()
            ->route('admin.kupon.index')
            ->with('success', "Kupon \"{$record->code}\" berhasil diperbarui.");
    }

    public function destroy(string $coupon): RedirectResponse
    {
        $record = $this->find($coupon);
        $code = $record->code;
        $record->delete();

        return redirect()
            ->route('admin.kupon.index')
            ->with('success', "Kupon \"{$code}\" berhasil dihapus.");
    }

    private function find(string $code): Coupon
    {
        return Coupon::with('branches')->where('code', $code)
            ->firstOr(fn () => abort(404, 'Kupon tidak ditemukan.'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $type = AdminOptions::toValue(AdminOptions::COUPON_TYPES, $data['type']);

        return [
            'code' => Str::upper($data['code']),
            'type' => $type,
            'value' => $type === 'ongkir gratis' ? 0 : $data['value'],
            'minimum_purchase' => $data['minimumPurchase'] ?? null,
            'quota' => $data['quota'] ?? null,
            'starts_at' => $data['startsAt'] ?? null,
            'expires_at' => $data['expiresAt'] ?? null,
            'status' => AdminOptions::toValue(AdminOptions::COUPON_STATUSES, $data['status']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function branchIds(array $data): array
    {
        return Branch::whereIn('code', $data['branches'] ?? [])->pluck('id')->all();
    }

    /**
     * @return array<string, list<string>>
     */
    private function formOptions(): array
    {
        return [
            'types' => AdminOptions::labels(AdminOptions::COUPON_TYPES),
            'statuses' => AdminOptions::labels(AdminOptions::COUPON_STATUSES),
            'branches' => Branch::orderBy('name')->get(['code', 'name'])
                ->map(fn (Branch $branch) => ['code' => $branch->code, 'name' => $branch->name])
                ->all(),
        ];
    }
}
