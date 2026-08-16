<?php

use App\Http\Controllers\Api\BranchLocatorController;
use Illuminate\Support\Facades\Route;

/**
 * Read-only JSON the storefront calls after the page has already rendered —
 * chiefly once the browser's geolocation prompt resolves. Nothing here needs
 * authentication; it is the same catalogue and branch data the Inertia pages
 * already share, just re-sortable without a full page reload.
 */
/*
 * These read the shopper's saved location out of the session (see
 * `LocationPreference`), and the `api` middleware group is stateless by
 * default — no session, no cookies. Pulling in `web` here gets the session
 * back without pulling in CSRF, which only guards state-changing verbs and
 * these are both GET.
 */
Route::middleware('web')->prefix('cabang')->name('api.cabang.')->group(function () {
    Route::get('terdekat', [BranchLocatorController::class, 'nearest'])->name('terdekat');
    Route::get('untuk-produk/{product}', [BranchLocatorController::class, 'forProduct'])->name('untuk-produk');
});
