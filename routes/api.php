<?php

use App\Http\Controllers\Api\BranchLocatorController;
use App\Http\Controllers\Api\ChatController;
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

/*
 * The chat widget. Stateless like the rest of the api group: the visitor is
 * identified by the token header, not a session. The limiters are defined in
 * AppServiceProvider.
 */
Route::prefix('chat')->name('api.chat.')->group(function () {
    Route::post('/', [ChatController::class, 'start'])->middleware('throttle:chat-start')->name('start');
    Route::get('/', [ChatController::class, 'show'])->middleware('throttle:chat-poll')->name('show');
    Route::post('pesan', [ChatController::class, 'send'])->middleware('throttle:chat-send')->name('send');
});
