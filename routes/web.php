<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\BranchStockController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\StockMatrixController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shop\BranchController as ShopBranchController;
use App\Http\Controllers\Shop\LocationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
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

    'atribut' => 'AttributeList',
    'atribut/tambah' => 'AttributeAdd',
    'atribut/ubah' => 'AttributeEdit',

    'pembelian' => 'PurchaseList',
    'pembelian/order' => 'PurchaseOrder',
    'pembelian/retur' => 'PurchaseReturns',

    'faktur' => 'InvoiceList',
    'faktur/detail' => 'InvoiceDetail',
    'faktur/tambah' => 'InvoiceAdd',
    'faktur/ubah' => 'InvoiceEdit',

    'kupon' => 'CouponList',
    'kupon/tambah' => 'CouponAdd',

    'peran' => 'RoleList',
    'peran/tambah' => 'RoleAdd',
    'peran/ubah' => 'RoleEdit',
    'hak-akses' => 'Permissions',

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
     * Sign-in sits outside the guard, or reaching it would loop.
     */
    Route::get('masuk', [AdminAuthController::class, 'show'])->name('masuk');
    Route::post('masuk', [AdminAuthController::class, 'login'])->name('masuk.store');
    Route::get('lupa-sandi', [AdminAuthController::class, 'forgotPassword'])->name('lupa-sandi');

    Route::middleware('admin')->group(function () use ($adminScreens) {
        Route::post('keluar', [AdminAuthController::class, 'logout'])->name('keluar');

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
        Route::prefix('pesanan')->name('pesanan.')->controller(OrderController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('reset', 'reset')->name('reset');
            Route::get('{order}', 'show')->name('show');
            Route::get('{order}/ubah', 'edit')->name('edit');
            Route::put('{order}', 'update')->name('update');
            Route::delete('{order}', 'destroy')->name('destroy');
        });

        Route::prefix('penjual')->name('penjual.')->controller(SellerController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('reset', 'reset')->name('reset');
            Route::get('{seller}', 'show')->name('show');
            Route::get('{seller}/ubah', 'edit')->name('edit');
            Route::put('{seller}', 'update')->name('update');
            Route::delete('{seller}', 'destroy')->name('destroy');
        });

        Route::prefix('pelanggan')->name('pelanggan.')->controller(CustomerController::class)->group(function () {
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

        Route::prefix('produk')->name('produk.')->controller(ProductController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('tambah', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('reset', 'reset')->name('reset');
            Route::get('{product}', 'show')->name('show');
            Route::get('{product}/ubah', 'edit')->name('edit');
            Route::put('{product}', 'update')->name('update');
            Route::delete('{product}', 'destroy')->name('destroy');
        });

        /**
         * Branch CRUD. A branch with stock on its shelves or orders in its
         * history cannot be deleted, only closed.
         */
        Route::prefix('cabang')->name('cabang.')->controller(AdminBranchController::class)->group(function () {
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
        Route::prefix('inventaris')->name('inventaris.')->group(function () {
            Route::prefix('stok')->name('stok.')->controller(BranchStockController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('{branch}', 'show')->name('show');
                Route::post('{branch}/{product}/sesuaikan', 'adjust')->name('adjust');
                Route::post('{branch}/{product}/terima', 'receive')->name('receive');
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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
     * Prototype sign-in: any email and password combination is accepted and the
     * resulting "session user" is kept in the session only. There is no user
     * record, no password check, and no auth guard behind this — it exists so the
     * screens can be clicked through as a flow.
     */
    Route::post('signin', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $request->session()->put('shop_user', [
            'name' => Str::of($credentials['email'])->before('@')->replace(['.', '_', '-'], ' ')->title()->value(),
            'email' => $credentials['email'],
        ]);

        return redirect()->route('home');
    })->name('signin.store');

    Route::post('signout', function (Request $request) {
        $request->session()->forget('shop_user');

        return redirect()->route('ui.signin');
    })->name('signout');

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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
