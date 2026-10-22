<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Shop\AddressController;
use App\Http\Controllers\Shop\AuthController as ShopAuthController;
use App\Http\Controllers\Shop\BranchController as ShopBranchController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\LocationController;
use App\Http\Controllers\Shop\NewsletterSubscribeController;
use App\Http\Controllers\Shop\NewsletterUnsubscribeController;
use App\Http\Controllers\Shop\OrderController as ShopOrderController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\PrivacyController;
use App\Http\Controllers\Shop\RegionController;
use App\Http\Controllers\Shop\ShippingController;
use App\Http\Controllers\Webhooks\BiteshipWebhookController;
use App\Http\Controllers\Webhooks\DokuWebhookController;
use App\Models\Branch;
use App\Support\DocumentPageContent;
use App\Support\FaqContent;
use App\Support\HomePageContent;
use App\Support\LocationPreference;
use App\Support\Presenters\StorefrontBranchPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Carousels, logos and texts are edited in the admin under Konten Website > Beranda.
Route::get('/', function (Request $request) {
    $coordinates = LocationPreference::coordinates($request);

    return Inertia::render('Shop/Home', [
        'content' => HomePageContent::forStorefront(),
        'pharmacies' => StorefrontBranchPresenter::cards(
            Branch::featured()->closestTo($coordinates['lat'] ?? null, $coordinates['lng'] ?? null)->get(),
        ),
    ]);
})->name('home');

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
    Route::post('lupa-sandi', [AdminAuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('lupa-sandi.store');
    Route::get('atur-ulang-sandi/{token}', [AdminAuthController::class, 'showResetPassword'])->name('atur-ulang-sandi');
    Route::post('atur-ulang-sandi', [AdminAuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('atur-ulang-sandi.store');

    Route::get('dua-faktor', [TwoFactorChallengeController::class, 'show'])->name('dua-faktor');
    Route::post('dua-faktor', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:10,1')->name('dua-faktor.store');

    /**
     * Every other /admin screen is the Filament panel now (see
     * App\Providers\Filament\AdminPanelProvider) — only the pre-panel auth
     * handshake above and sign-out below stay outside it, since
     * EnsureAdminIsAuthenticated redirects here rather than to a
     * Filament-generated login page.
     */
    Route::middleware('admin')->group(function () {
        Route::post('keluar', [AdminAuthController::class, 'logout'])->name('keluar');
        Route::post('bahasa/{locale}', LocaleController::class)->name('bahasa');
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
    'reviews' => 'Reviews',
    'leave-a-review' => 'LeaveAReview',
];

/**
 * The storefront used to live under /ui. Old bookmarks, emails and search
 * results still point there, so send them to the same page without the prefix.
 */
/**
 * Newsletter opt-out. Public and token-based: the token in the email is the
 * only thing identifying the subscriber. The POST is exempt from CSRF
 * (bootstrap/app.php) so a mail app's one-click unsubscribe request works.
 */
Route::post('newsletter/berlangganan', NewsletterSubscribeController::class)->middleware('throttle:6,1')->name('newsletter.subscribe');
Route::get('newsletter/berhenti/{token}', [NewsletterUnsubscribeController::class, 'show'])->name('newsletter.unsubscribe');
Route::post('newsletter/berhenti/{token}', [NewsletterUnsubscribeController::class, 'destroy'])->name('newsletter.unsubscribe.store');

Route::get('ui/{path?}', function (Request $request, ?string $path = null) {
    $query = $request->getQueryString();

    return redirect('/'.ltrim((string) $path, '/').($query ? '?'.$query : ''), 301);
})->where('path', '.*');

Route::name('ui.')->group(function () use ($beShopScreens) {
    // The developer screen index. The storefront no longer lives under /ui,
    // so it can't take `/` (that is the homepage).
    Route::get('screens', fn () => Inertia::render('Shop/Index'))->name('index');

    foreach ($beShopScreens as $slug => $component) {
        Route::get($slug, fn () => Inertia::render("Shop/{$component}"))->name($slug);
    }

    /*
     * Content pages, edited in the admin under Konten Website. Syarat &
     * Ketentuan and Kebijakan Privasi are popups (Components/Shop/LegalModal.tsx),
     * not pages, so they load their text from `konten/dokumen/{document}`.
     */
    Route::get('faq', fn () => Inertia::render('Shop/Faq', ['faqs' => FaqContent::forStorefront()]))->name('faq');
    Route::get('shipping-info', fn () => Inertia::render('Shop/ShippingInfo', [
        'document' => DocumentPageContent::forStorefront(DocumentPageContent::SHIPPING),
    ]))->name('shipping-info');
    Route::get('kebijakan-pengembalian-dana', fn () => Inertia::render('Shop/RefundPolicy', [
        'document' => DocumentPageContent::forStorefront(DocumentPageContent::REFUND),
    ]))->name('kebijakan-pengembalian-dana');
    Route::get('konten/dokumen/{document}', fn (string $document) => response()->json(DocumentPageContent::forStorefront($document)))
        ->whereIn('document', DocumentPageContent::LEGAL)
        ->name('konten.dokumen');

    /**
     * Customer auth (Fase 3.3) — the `customer` guard. `signin`/`signup` etc.
     * as GET pages already come from the `$beShopScreens` loop above; these
     * are the actions those forms post to.
     */
    Route::post('signin', [ShopAuthController::class, 'login'])->name('signin.store');
    Route::post('daftar', [ShopAuthController::class, 'register'])->middleware('throttle:6,1')->name('daftar.store');
    Route::post('lupa-sandi', [ShopAuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('forgot-password.store');
    Route::post('atur-ulang-sandi', [ShopAuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('reset-password.store');

    Route::get('verifikasi-email/{id}/{hash}', [ShopAuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verifikasi-email');

    Route::middleware('customer')->group(function () {
        Route::post('signout', [ShopAuthController::class, 'logout'])->name('signout');
        Route::post('kirim-verifikasi-email', [ShopAuthController::class, 'sendVerificationEmail'])
            ->middleware('throttle:6,1')
            ->name('kirim-verifikasi-email');
        Route::post('verify-phone', [ShopAuthController::class, 'sendPhoneOtp'])->middleware('throttle:3,10')->name('verify-phone.store');
        Route::post('otp-code', [ShopAuthController::class, 'verifyPhoneOtp'])->middleware('throttle:10,1')->name('otp-code.store');
        Route::post('otp-code/kirim-ulang', [ShopAuthController::class, 'resendPhoneOtp'])->middleware('throttle:3,10')->name('otp-code.resend');
    });

    /**
     * Each apotek's own page, linked from its homepage card. The old
     * "Cabang Kami" list was removed; its address now leads to the homepage,
     * where the apotek cards are, so saved links and banners keep working.
     */
    Route::get('apotek/{branch:slug}', [ShopBranchController::class, 'show'])->name('apotek.show');
    Route::permanentRedirect('cabang-kami', '/');

    /**
     * The shopper's location is saved through `LocationController` so it can
     * be set from anywhere (the geolocation prompt, the pickup popup).
     */
    Route::post('lokasi', [LocationController::class, 'store'])->name('lokasi.store');
    Route::delete('lokasi', [LocationController::class, 'destroy'])->name('lokasi.destroy');

    /**
     * Cart (Fase 5.3). Guests can add/change/remove items — see
     * `CartManager` — so only the routes below `customer` actually require
     * signing in.
     */
    Route::get('cart', [CartController::class, 'index'])->name('cart');
    Route::get('keranjang/ringkas', [CartController::class, 'preview'])->name('keranjang.ringkas');
    Route::post('keranjang', [CartController::class, 'store'])->name('keranjang.store');
    Route::patch('keranjang/{product}', [CartController::class, 'update'])->name('keranjang.update');
    Route::delete('keranjang/{product}', [CartController::class, 'destroy'])->name('keranjang.destroy');

    Route::get('order-successful', [ShopOrderController::class, 'success'])->name('order-successful');

    // Public — the Provinsi/Kota/Kecamatan/Kelurahan cascade used by both
    // `Shop/AddNewAddress.tsx` (signed-in) and the checkout page
    // (not yet signed in), and open government region data either way.
    Route::get('wilayah', [RegionController::class, 'children'])->name('wilayah');

    /**
     * Checkout is open to guests (Fase 0's "boleh checkout sebagai tamu?",
     * decided: yes). One page and one submit for everyone: a guest's contact
     * details and address are part of the same form, and `CheckoutController`
     * creates and signs in their account only once the order is valid.
     */
    Route::get('checkout/tamu', fn () => redirect()->route('ui.checkout'))->name('checkout.tamu');
    Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('checkout/ongkir', [ShippingController::class, 'rates'])->middleware('throttle:30,1')->name('checkout.ongkir');

    Route::middleware('customer')->group(function () {
        Route::get('profile', fn () => Inertia::render('Shop/Profile'))->name('profile');

        Route::post('keranjang/kupon', [CartController::class, 'applyCoupon'])->name('keranjang.kupon.store');
        Route::delete('keranjang/kupon', [CartController::class, 'removeCoupon'])->name('keranjang.kupon.destroy');

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
