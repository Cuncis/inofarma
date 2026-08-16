<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\LocationPreference;
use App\Support\Presenters\StorefrontBranchPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Cabang Kami" — every active branch, nearest first when we know where the
 * shopper is.
 */
class BranchController extends Controller
{
    public function index(Request $request): Response
    {
        $coordinates = LocationPreference::coordinates($request);
        $area = $coordinates ? null : LocationPreference::area($request);

        $query = Branch::query()->active();

        if ($coordinates) {
            $query->nearest($coordinates['lat'], $coordinates['lng']);
        } elseif ($area && $area['kota']) {
            $query->where('kota', $area['kota'])->orderBy('name');
        } else {
            $query->orderBy('kota')->orderBy('name');
        }

        return Inertia::render('Shop/OurBranches', [
            'branches' => StorefrontBranchPresenter::list($query->get()),
            // Areas a shopper can actually pick from, for the fallback picker —
            // derived from real coverage rather than a nationwide administrative
            // list we don't need yet.
            'areas' => Branch::active()
                ->orderBy('provinsi')->orderBy('kota')
                ->get(['provinsi', 'kota'])
                ->unique(fn (Branch $branch) => $branch->provinsi.'|'.$branch->kota)
                ->map(fn (Branch $branch) => ['provinsi' => $branch->provinsi, 'kota' => $branch->kota])
                ->values()
                ->all(),
            'hasLocation' => (bool) ($coordinates ?? $area),
        ]);
    }
}
