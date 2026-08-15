<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('BeShop/Home'))->name('home');

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

    'produk' => 'ProductList',
    'produk/grid' => 'ProductGrid',
    'produk/detail' => 'ProductDetail',
    'produk/tambah' => 'ProductAdd',
    'produk/ubah' => 'ProductEdit',

    'kategori' => 'CategoryList',
    'kategori/detail' => 'CategoryDetail',
    'kategori/tambah' => 'CategoryAdd',
    'kategori/ubah' => 'CategoryEdit',

    'atribut' => 'AttributeList',
    'atribut/tambah' => 'AttributeAdd',
    'atribut/ubah' => 'AttributeEdit',

    'inventaris/gudang' => 'InventoryWarehouse',
    'inventaris/pesanan-masuk' => 'InventoryReceivedOrders',

    'pesanan' => 'OrderList',
    'pesanan/detail' => 'OrderDetail',
    'pesanan/keranjang' => 'OrderCart',
    'pesanan/checkout' => 'OrderCheckout',

    'pembelian' => 'PurchaseList',
    'pembelian/order' => 'PurchaseOrder',
    'pembelian/retur' => 'PurchaseReturns',

    'faktur' => 'InvoiceList',
    'faktur/detail' => 'InvoiceDetail',
    'faktur/tambah' => 'InvoiceAdd',
    'faktur/ubah' => 'InvoiceEdit',

    'pelanggan' => 'CustomerList',
    'pelanggan/detail' => 'CustomerDetail',
    'pelanggan/tambah' => 'CustomerAdd',
    'pelanggan/ubah' => 'CustomerEdit',

    'penjual' => 'SellerList',
    'penjual/detail' => 'SellerDetail',
    'penjual/tambah' => 'SellerAdd',
    'penjual/ubah' => 'SellerEdit',

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

    'auth/masuk' => 'AuthSignIn',
    'auth/daftar' => 'AuthSignUp',
    'auth/atur-ulang-sandi' => 'AuthPassword',
    'auth/kunci-layar' => 'AuthLockScreen',

    'halaman/selamat-datang' => 'Starter',
    'halaman/segera-hadir' => 'ComingSoon',
    'halaman/linimasa' => 'Timeline',
    'halaman/harga' => 'Pricing',
    'halaman/pemeliharaan' => 'Maintenance',
    'halaman/404' => 'Error404',
    'halaman/404-alt' => 'Error404Alt',
];

Route::prefix('admin')->name('admin.')->group(function () use ($adminScreens) {
    foreach ($adminScreens as $slug => $component) {
        $path = $slug === '/' ? '/' : $slug;
        $name = $slug === '/' ? 'dashboard' : str_replace('/', '.', $slug);

        Route::get($path, fn () => Inertia::render("Admin/{$component}"))->name($name);
    }
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * BeShop UI screens.
 *
 * Layout-only Inertia pages, one per screen from the design reference. The slug
 * order here mirrors `resources/js/Components/BeShop/screens.js`.
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
    Route::get('/', fn () => Inertia::render('BeShop/Index'))->name('index');

    foreach ($beShopScreens as $slug => $component) {
        Route::get($slug, fn () => Inertia::render("BeShop/{$component}"))->name($slug);
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

        $request->session()->put('beshop_user', [
            'name' => Str::of($credentials['email'])->before('@')->replace(['.', '_', '-'], ' ')->title()->value(),
            'email' => $credentials['email'],
        ]);

        return redirect()->route('home');
    })->name('signin.store');

    Route::post('signout', function (Request $request) {
        $request->session()->forget('beshop_user');

        return redirect()->route('ui.signin');
    })->name('signout');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
