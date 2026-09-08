<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Shop\AddressController;
use App\Http\Controllers\Shop\AuthController as ShopAuthController;
use App\Http\Controllers\Shop\BranchController as ShopBranchController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\GuestCheckoutController;
use App\Http\Controllers\Shop\LocationController;
use App\Http\Controllers\Shop\OrderController as ShopOrderController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\PrivacyController;
use App\Http\Controllers\Shop\RegionController;
use App\Http\Controllers\Shop\ShippingController;
use App\Http\Controllers\Webhooks\BiteshipWebhookController;
use App\Http\Controllers\Webhooks\DokuWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Shop/Home'))->name('home');

/**
 * DOKU's server-to-server notification (Fase 6). Lives outside `ui`/`admin`
 * on purpose — it isn't a page for either audience, and its path must match
 * `services.doku.notification_path` exactly, byte for byte, since that path
 * is itself part of the signature DOKU computes. CSRF is exempted for this
 * one path in `bootstrap/app.php`; `DokuWebhookController` verifies DOKU's
 * own HMAC signature instead.
 */
Route::post('doku/notifikasi', [DokuWebhookController::class, 'handle'])->name('doku.notifikasi');

/**
 * Biteship's server-to-server notification (Fase 7) — see
 * `BiteshipWebhookController` for why `?token=` rather than a signature is
 * what proves this actually came from Biteship.
 */
Route::post('biteship/notifikasi', [BiteshipWebhookController::class, 'handle'])->name('biteship.notifikasi');

Route::prefix('admin')->name('admin.')->group(function () {
    /**
     * Sign-in and password recovery sit outside the guard, or reaching them
     * would loop. The 2FA challenge is a special case: the user has passed
     * their password but isn't logged in yet (see `AdminAuthController::login()`
     * and `TwoFactorChallengeController`), so it can't sit behind `admin` either.
     */
    Route::get('masuk', [AdminAuthController::class, 'show'])->name('masuk');
    Route::post('masuk', [AdminAuthController::class, 'login'])->name('masuk.store');
    Route::get('lupa-sandi', [AdminAuthController::class, 'forgotPassword'])->name('lupa-sandi');
    Route::post('lupa-sandi', [AdminAuthController::class, 'sendResetLink'])->name('lupa-sandi.store');
    Route::get('atur-ulang-sandi/{token}', [AdminAuthController::class, 'showResetPassword'])->name('atur-ulang-sandi');
    Route::post('atur-ulang-sandi', [AdminAuthController::class, 'resetPassword'])->name('atur-ulang-sandi.store');

    Route::get('dua-faktor', [TwoFactorChallengeController::class, 'show'])->name('dua-faktor');
    Route::post('dua-faktor', [TwoFactorChallengeController::class, 'store'])->name('dua-faktor.store');

    /**
     * Every other /admin screen is the Filament panel now (see
     * App\Providers\Filament\AdminPanelProvider) — only the pre-panel auth
     * handshake above and sign-out below stay outside it, since
     * EnsureAdminIsAuthenticated redirects here rather than to a
     * Filament-generated login page.
     */
    Route::middleware('admin')->group(function () {
        Route::post('keluar', [AdminAuthController::class, 'logout'])->name('keluar');
    });
});

/**
 * Inofarma UI screens.
 *
 * Layout-only Inertia pages, one per screen from the design reference. The slug
 * order here mirrors `resources/js/Components/Shop/screens.js`.
 *
 * @var array<string, string>
 */
$beShopScreens = [
    'signin' => 'SignIn',
    'signup' => 'SignUp',
    'forgot-password' => 'ForgotPassword',
    'verify-phone' => 'VerifyPhone',
    'otp-code' => 'OtpCode',
    'account-created' => 'AccountCreated',
    'email-sent' => 'EmailSent',
    'new-password' => 'NewPassword',
    'home' => 'Home',
    'wishlist' => 'Wishlist',
    'categories' => 'Categories',
    'shop' => 'Shop',
    'product-detail' => 'ProductDetail',
    'filter' => 'Filter',
    'order-failed' => 'OrderFailed',
    'cart-empty' => 'CartEmpty',
    'wishlist-empty' => 'WishlistEmpty',
    'promocodes-empty' => 'PromocodesEmpty',
    'order-history-empty' => 'OrderHistoryEmpty',
    'my-promocodes' => 'MyPromocodes',
    'shipping-info' => 'ShippingInfo',
    'faq' => 'Faq',
    'reviews' => 'Reviews',
    'leave-a-review' => 'LeaveAReview',

    // Fase 9.1 — legal pages, public (a visitor reads these before signing up).
    'syarat-ketentuan' => 'Terms',
    'kebijakan-privasi' => 'PrivacyPolicy',
    'kebijakan-pengembalian-dana' => 'RefundPolicy',
    'tentang-kami' => 'AboutUs',
];

Route::prefix('ui')->name('ui.')->group(function () use ($beShopScreens) {
    Route::get('/', fn () => Inertia::render('Shop/Index'))->name('index');

    foreach ($beShopScreens as $slug => $component) {
        Route::get($slug, fn () => Inertia::render("Shop/{$component}"))->name($slug);
    }

    /**
     * Customer auth (Fase 3.3) — the `customer` guard. `signin`/`signup` etc.
     * as GET pages already come from the `$beShopScreens` loop above; these
     * are the actions those forms post to.
     */
    Route::post('signin', [ShopAuthController::class, 'login'])->name('signin.store');
    Route::post('daftar', [ShopAuthController::class, 'register'])->name('daftar.store');
    Route::post('lupa-sandi', [ShopAuthController::class, 'sendResetLink'])->name('forgot-password.store');
    Route::post('atur-ulang-sandi', [ShopAuthController::class, 'resetPassword'])->name('reset-password.store');

    Route::get('verifikasi-email/{id}/{hash}', [ShopAuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verifikasi-email');

    Route::middleware('customer')->group(function () {
        Route::post('signout', [ShopAuthController::class, 'logout'])->name('signout');
        Route::post('kirim-verifikasi-email', [ShopAuthController::class, 'sendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('kirim-verifikasi-email');
        Route::post('verify-phone', [ShopAuthController::class, 'sendPhoneOtp'])->name('verify-phone.store');
        Route::post('otp-code', [ShopAuthController::class, 'verifyPhoneOtp'])->name('otp-code.store');
        Route::post('otp-code/kirim-ulang', [ShopAuthController::class, 'resendPhoneOtp'])->name('otp-code.resend');
    });

    /**
     * "Cabang Kami" — every branch, nearest first once we know where the
     * shopper is. The location itself is saved through `LocationController`,
     * not through this page, so it can be set from anywhere (the geolocation
     * prompt, the product page's branch picker) and not just from here.
     */
    Route::get('cabang-kami', [ShopBranchController::class, 'index'])->name('cabang-kami');

    Route::post('lokasi', [LocationController::class, 'store'])->name('lokasi.store');
    Route::delete('lokasi', [LocationController::class, 'destroy'])->name('lokasi.destroy');

    /**
     * Cart (Fase 5.3). Guests can add/change/remove items — see
     * `CartManager` — so only the routes below `customer` actually require
     * signing in.
     */
    Route::get('cart', [CartController::class, 'index'])->name('cart');
    Route::post('keranjang', [CartController::class, 'store'])->name('keranjang.store');
    Route::patch('keranjang/{product}', [CartController::class, 'update'])->name('keranjang.update');
    Route::delete('keranjang/{product}', [CartController::class, 'destroy'])->name('keranjang.destroy');

    Route::get('order-successful', fn (Request $request) => Inertia::render('Shop/OrderSuccessful', [
        'orderNumber' => $request->query('nomor'),
    ]))->name('order-successful');

    // Public — the Provinsi/Kota/Kecamatan/Kelurahan cascade used by both
    // `Shop/AddNewAddress.jsx` (signed-in) and `Shop/GuestCheckout.jsx`
    // (not yet signed in), and open government region data either way.
    Route::get('wilayah', [RegionController::class, 'children'])->name('wilayah');

    /**
     * Checkout without an account (Fase 0's "boleh checkout sebagai tamu?",
     * decided: yes). Public on purpose — `GuestCheckoutController` creates
     * and signs the guest in itself, then hands off to the normal
     * `customer`-gated checkout below. A signed-in customer redirected here
     * bounces straight back to `ui.checkout`.
     */
    Route::get('checkout/tamu', [GuestCheckoutController::class, 'create'])->name('checkout.tamu');
    Route::post('checkout/tamu', [GuestCheckoutController::class, 'store'])->name('checkout.tamu.store');

    Route::middleware('customer')->group(function () {
        Route::get('profile', fn () => Inertia::render('Shop/Profile'))->name('profile');

        Route::post('keranjang/kupon', [CartController::class, 'applyCoupon'])->name('keranjang.kupon.store');
        Route::delete('keranjang/kupon', [CartController::class, 'removeCoupon'])->name('keranjang.kupon.destroy');

        Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('checkout/ongkir', [ShippingController::class, 'rates'])->name('checkout.ongkir');

        Route::get('shipping-details', [AddressController::class, 'forCheckout'])->name('shipping-details');
        Route::post('shipping-details', [AddressController::class, 'selectForCheckout'])->name('shipping-details.store');

        Route::get('edit-profile', [AddressController::class, 'forProfile'])->name('edit-profile');

        Route::get('my-address', [AddressController::class, 'index'])->name('my-address');
        Route::get('add-new-address', [AddressController::class, 'create'])->name('add-new-address');
        Route::post('add-new-address', [AddressController::class, 'store'])->name('add-new-address.store');
        Route::delete('alamat/{address}', [AddressController::class, 'destroy'])->name('alamat.destroy');
        Route::post('alamat/{address}/utama', [AddressController::class, 'makeDefault'])->name('alamat.utama');

        Route::prefix('privasi-saya')->name('privasi-saya.')->controller(PrivacyController::class)->group(function () {
            Route::get('/', 'show')->name('index');
            Route::get('unduh', 'export')->name('unduh');
            Route::delete('/', 'destroyAccount')->name('hapus');
        });

        Route::get('order-history', [ShopOrderController::class, 'index'])->name('order-history');
        Route::get('pesanan/{order}', [ShopOrderController::class, 'show'])->name('pesanan.show');
        Route::get('track-order/{order}', [ShopOrderController::class, 'track'])->name('track-order');
        Route::post('pesanan/{order}/batalkan', [ShopOrderController::class, 'cancel'])->name('pesanan.batalkan');
        Route::post('pesanan/{order}/bayar', [PaymentController::class, 'create'])->name('pesanan.bayar');
    });
});
