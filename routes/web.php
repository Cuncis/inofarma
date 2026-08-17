<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\BranchStockController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StockMatrixController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TwoFactorChallengeController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Shop\AuthController as ShopAuthController;
use App\Http\Controllers\Shop\BranchController as ShopBranchController;
use App\Http\Controllers\Shop\LocationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Shop/Home'))->name('home');

/**
 * Admin screens.
 *
 * Layout-only Inertia pages converted from the source HTML theme. The slug order
 * mirrors `resources/js/Components/Admin/nav.js`.
 *
 * @var array<string, string>
 */
$adminScreens = [
    '/' => 'Dashboard',
    'dasbor/penjualan' => 'DashboardSales',
    'dasbor/keuangan' => 'DashboardFinance',

    'pembelian' => 'PurchaseList',
    'pembelian/order' => 'PurchaseOrder',
    'pembelian/retur' => 'PurchaseReturns',

    'ulasan' => 'Reviews',
    'profil' => 'Profile',
    'pengaturan' => 'Settings',

    'chat' => 'Chat',
    'email' => 'Email',
    'kalender' => 'Calendar',
    'todo' => 'Todo',

    'bantuan' => 'HelpCenter',
    'faq' => 'Faq',
    'kebijakan-privasi' => 'PrivacyPolicy',

];

Route::prefix('admin')->name('admin.')->group(function () use ($adminScreens) {
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

    Route::middleware('admin')->group(function () use ($adminScreens) {
        Route::post('keluar', [AdminAuthController::class, 'logout'])->name('keluar');

        Route::prefix('keamanan')->name('keamanan.')->controller(TwoFactorController::class)->group(function () {
            Route::get('/', 'show')->name('index');
            Route::post('aktifkan', 'enable')->name('aktifkan');
            Route::post('konfirmasi', 'confirm')->name('konfirmasi');
            Route::delete('/', 'disable')->name('nonaktifkan');
            Route::post('kode-pemulihan', 'regenerateRecoveryCodes')->name('kode-pemulihan');
        });

        /**
         * Staf: the `users` rows admin sign-in actually checks, each with a
         * branch (null = pusat) and one or more roles (Fase 3.2).
         */
        Route::prefix('staf')->name('staf.')->controller(StaffController::class)
            ->middleware('permission:Pengaturan:Ubah')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{staff}/ubah', 'edit')->name('edit');
                Route::put('{staff}', 'update')->name('update');
                Route::delete('{staff}', 'destroy')->name('destroy');
            });

        Route::prefix('peran')->name('peran.')->controller(RoleController::class)
            ->middleware('permission:Peran:Lihat')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store')->middleware('permission:Peran:Ubah');
                Route::get('{role}/ubah', 'edit')->name('edit');
                Route::put('{role}', 'update')->name('update')->middleware('permission:Peran:Ubah');
                Route::delete('{role}', 'destroy')->name('destroy')->middleware('permission:Peran:Ubah');
            });

        Route::get('hak-akses', [RoleController::class, 'matrix'])->name('hak-akses')
            ->middleware('permission:Peran:Lihat');
        Route::post('hak-akses', [RoleController::class, 'updateMatrix'])->name('hak-akses.store')
            ->middleware('permission:Peran:Ubah');

        /**
         * Product CRUD. Backed by the session store rather than a database, but the
         * routes are the ones a real resource would expose.
         */
        /**
         * Category CRUD. Deleting is refused while products still reference the
         * category, so `destroy` can come back with an error rather than a success.
         */
        /**
         * Customer CRUD. Deleting is refused while order history exists.
         */
        /**
         * Seller CRUD. Deleting is refused while the seller still stocks products.
         */
        /**
         * Order CRUD. A completed order cannot be deleted, only cancelled.
         */
        Route::prefix('pesanan')->name('pesanan.')->controller(OrderController::class)
            ->middleware('permission:Pesanan:Lihat')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::post('reset', 'reset')->name('reset');
                Route::get('{order}', 'show')->name('show');
                Route::get('{order}/ubah', 'edit')->name('edit');
                Route::put('{order}', 'update')->name('update');
                Route::delete('{order}', 'destroy')->name('destroy');
            });

        /**
         * "Pemasok" — distributor/PBF that supplies stock to a branch, not a
         * marketplace seller (utang teknis #5). The route segment carries the
         * new name; `{supplier}` route params still bind by `Supplier::code`.
         */
        Route::prefix('pemasok')->name('pemasok.')->controller(SupplierController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('reset', 'reset')->name('reset');
            Route::get('{supplier}', 'show')->name('show');
            Route::get('{supplier}/ubah', 'edit')->name('edit');
            Route::put('{supplier}', 'update')->name('update');
            Route::delete('{supplier}', 'destroy')->name('destroy');
        });

        Route::prefix('atribut')->name('atribut.')->controller(AttributeController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{attribute}/ubah', 'edit')->name('edit');
            Route::put('{attribute}', 'update')->name('update');
            Route::delete('{attribute}', 'destroy')->name('destroy');
        });

        Route::prefix('kupon')->name('kupon.')->controller(CouponController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{coupon}/ubah', 'edit')->name('edit');
            Route::put('{coupon}', 'update')->name('update');
            Route::delete('{coupon}', 'destroy')->name('destroy');
        });

        Route::prefix('faktur')->name('faktur.')->controller(InvoiceController::class)
            ->middleware('permission:Pesanan:Lihat')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{order}', 'show')->name('show');
            });

        Route::prefix('pelanggan')->name('pelanggan.')->controller(CustomerController::class)
            ->middleware('permission:Pelanggan:Lihat')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::post('reset', 'reset')->name('reset');
                Route::get('{customer}', 'show')->name('show');
                Route::get('{customer}/ubah', 'edit')->name('edit');
                Route::put('{customer}', 'update')->name('update');
                Route::delete('{customer}', 'destroy')->name('destroy');
            });

        Route::prefix('kategori')->name('kategori.')->controller(CategoryController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('reset', 'reset')->name('reset');
            Route::get('{category}', 'show')->name('show');
            Route::get('{category}/ubah', 'edit')->name('edit');
            Route::put('{category}', 'update')->name('update');
            Route::delete('{category}', 'destroy')->name('destroy');
        });

        Route::prefix('produk')->name('produk.')->controller(ProductController::class)
            ->middleware('permission:Produk:Lihat')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::post('reset', 'reset')->name('reset');
                Route::get('{product}', 'show')->name('show');
                Route::get('{product}/ubah', 'edit')->name('edit');
                Route::put('{product}', 'update')->name('update');
                Route::delete('{product}', 'destroy')->name('destroy');
            });

        Route::prefix('produk/{product}/gambar')->name('produk.gambar.')
            ->controller(ProductImageController::class)
            ->middleware('permission:Produk:Lihat')
            ->group(function () {
                Route::post('/', 'store')->name('store');
                Route::post('urutkan', 'reorder')->name('urutkan');
                Route::post('{image}/utama', 'makePrimary')->name('utama');
                Route::delete('{image}', 'destroy')->name('destroy');
            });

        /**
         * Branch CRUD. A branch with stock on its shelves or orders in its
         * history cannot be deleted, only closed.
         */
        Route::prefix('cabang')->name('cabang.')->controller(AdminBranchController::class)
            ->middleware('permission:Cabang:Lihat')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::post('reset', 'reset')->name('reset');
                Route::get('{branch}', 'show')->name('show');
                Route::get('{branch}/ubah', 'edit')->name('edit');
                Route::put('{branch}', 'update')->name('update');
                Route::delete('{branch}', 'destroy')->name('destroy');
            });

        /**
         * Inventory across branches: per-branch stock (view/adjust/receive), the
         * product × branch matrix, and stock transfers between branches.
         */
        Route::prefix('inventaris')->name('inventaris.')->middleware('permission:Inventaris:Lihat')->group(function () {
            Route::prefix('stok')->name('stok.')->controller(BranchStockController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{branch}', 'show')->name('show');
                Route::post('{branch}/{product}/sesuaikan', 'adjust')->name('adjust')
                    ->middleware('permission:Inventaris:Sesuaikan Stok');
                Route::post('{branch}/{product}/terima', 'receive')->name('receive')
                    ->middleware('permission:Inventaris:Terima Barang');
            });

            Route::get('matriks', [StockMatrixController::class, 'index'])->name('matriks');

            Route::prefix('transfer')->name('transfer.')->controller(StockTransferController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('tambah', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('{transfer}', 'show')->name('show');
                Route::post('{transfer}/kirim', 'ship')->name('ship');
                Route::post('{transfer}/terima', 'receive')->name('receive');
                Route::post('{transfer}/batalkan', 'cancel')->name('cancel');
            });
        });

        foreach ($adminScreens as $slug => $component) {
            $path = $slug === '/' ? '/' : $slug;
            $name = $slug === '/' ? 'dashboard' : str_replace('/', '.', $slug);

            Route::get($path, fn () => Inertia::render("Admin/{$component}"))->name($name);
        }
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
    'cart' => 'Cart',
    'wishlist' => 'Wishlist',
    'profile' => 'Profile',
    'categories' => 'Categories',
    'shop' => 'Shop',
    'product-detail' => 'ProductDetail',
    'filter' => 'Filter',
    'checkout' => 'Checkout',
    'shipping-details' => 'ShippingDetails',
    'payment-method' => 'PaymentMethod',
    'order-successful' => 'OrderSuccessful',
    'order-failed' => 'OrderFailed',
    'cart-empty' => 'CartEmpty',
    'wishlist-empty' => 'WishlistEmpty',
    'promocodes-empty' => 'PromocodesEmpty',
    'order-history-empty' => 'OrderHistoryEmpty',
    'edit-profile' => 'EditProfile',
    'payment-methods' => 'PaymentMethods',
    'add-new-card' => 'AddNewCard',
    'my-address' => 'MyAddress',
    'add-new-address' => 'AddNewAddress',
    'my-promocodes' => 'MyPromocodes',
    'order-history' => 'OrderHistory',
    'track-order' => 'TrackOrder',
    'shipping-info' => 'ShippingInfo',
    'faq' => 'Faq',
    'reviews' => 'Reviews',
    'leave-a-review' => 'LeaveAReview',
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
});
