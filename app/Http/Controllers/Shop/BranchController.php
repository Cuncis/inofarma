<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Support\Presenters\StorefrontBranchPresenter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * One apotek's own page, opened from its card in "Apotek Inofarma Terdekat"
 * on the homepage: its licences, pharmacist, hours, and the other apotek
 * nearest to it.
 */
class BranchController extends Controller
{
    public function show(Branch $branch): Response
    {
        abort_if($branch->status === 'tutup permanen', 404);

        $latitude = $branch->latitude !== null ? (float) $branch->latitude : null;
        $longitude = $branch->longitude !== null ? (float) $branch->longitude : null;

        return Inertia::render('Shop/BranchDetail', [
            'branch' => StorefrontBranchPresenter::page($branch),
            'nearby' => StorefrontBranchPresenter::cards(
                Branch::featured()->whereKeyNot($branch->id)->closestTo($latitude, $longitude)->limit(3)->get(),
            ),
        ]);
    }
}
