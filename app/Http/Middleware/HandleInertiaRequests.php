<?php

namespace App\Http\Middleware;

use App\Support\Cart\CartManager;
use App\Support\Presenters\AdminNotificationPresenter;
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
            'shopUser' => function () use ($request) {
                $customer = $request->user('customer');

                return $customer ? ['name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone] : null;
            },
            'adminUser' => function () use ($request) {
                $user = $request->user('web');

                return $user ? ['name' => $user->name, 'email' => $user->email] : null;
            },

            // The admin topbar's bell (Fase 8) — null for the storefront and
            // for a guest, so the closure never queries for either.
            'adminNotifications' => function () use ($request) {
                $user = $request->user('web');

                if (! $user) {
                    return null;
                }

                return [
                    'unreadCount' => $user->unreadNotifications()->count(),
                    'items' => AdminNotificationPresenter::collection(
                        $user->notifications()->latest()->limit(10)->get()
                    ),
                ];
            },
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

            // Badge count on the "Pesanan" tab — cheap for a guest (a session
            // read) and one query for a signed-in customer.
            'cartCount' => fn () => (new CartManager($request))->count(),
        ];
    }
}
