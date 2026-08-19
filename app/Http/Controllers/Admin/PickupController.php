<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Pickup\PickupCodeException;
use App\Support\Pickup\PickupCodeService;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The branch counter's own screen (Fase 7.2): every order currently
 * `siap diambil` — scoped to the signed-in staff member's own branch for
 * free by `Order`'s `BranchScope`, same as every other admin order query —
 * and the code-entry form that hands one over.
 */
class PickupController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with(['items', 'customer', 'branch'])
            ->where('status', 'siap diambil')
            ->orderBy('ready_at')
            ->get();

        return Inertia::render('Admin/PickupQueue', [
            'orders' => OrderPresenter::collection($orders),
            'prefill' => [
                'order' => $request->query('order'),
                'code' => $request->query('kode'),
            ],
        ]);
    }

    public function handOver(Request $request, string $order): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'max:10']]);

        $record = Order::where('number', $order)
            ->firstOr(fn () => abort(404, 'Pesanan tidak ditemukan.'));

        try {
            PickupCodeService::handOver($record, $validated['code'], $request->user());
        } catch (PickupCodeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.pengambilan.index')
            ->with('success', "Pesanan #{$record->number} berhasil diserahkan.");
    }
}
