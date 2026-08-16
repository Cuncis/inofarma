<?php

namespace App\Http\Middleware;

use App\Support\Presenters\ShopCatalogPresenter;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'shopUser' => fn () => $request->session()->get('shop_user'),
            'adminUser' => fn () => $request->session()->get('admin_user'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            /*
             * The catalogue, shared rather than passed per screen: the search
             * overlay sits in the storefront layout and can be opened from any
             * page, so it has to be available everywhere. The admin builds its
             * own props per screen and gets this only for the global search.
             */
            'catalog' => fn () => ShopCatalogPresenter::forStorefront(),
        ];
    }
}
