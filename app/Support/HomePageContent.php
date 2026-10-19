<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Str;

/**
 * The storefront home page's editable content: carousels, the "Semua Produk"
 * icon, brand logos, benefits, testimonials and the closing texts. Edited in
 * the admin under Konten Website > Beranda and stored through
 * {@see ContentStore}. A section nobody has saved yet shows {@see DEFAULTS}, the
 * content the page shipped with.
 *
 * The category icons are not here: each one is the `image_path` of its
 * `Category`, so the icon and the shop filter it links to always agree.
 *
 * Images are stored as the URL the page shows: `/media/...` for artwork that
 * ships with the site, or the uploads disk URL for a file uploaded here.
 */
class HomePageContent
{
    public const KEY = 'content.home';

    /** Uploaded images go here on the uploads disk, apart from `media/` so Media File cannot delete them. */
    public const UPLOAD_DIRECTORY = 'content';

    public const IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/avif'];

    /** Largest accepted image, in kilobytes. */
    public const MAX_IMAGE_SIZE = 4096;

    public const CONTACT_ICONS = ['phone', 'email'];

    /** The section headings, in the order they appear on the page. */
    public const HEADINGS = [
        'recommended' => 'Rekomendasi Untukmu',
        'newArrivals' => 'Produk Kesehatan Terbaru',
        'trending' => 'Produk Terlaris Kami',
        'brands' => 'Brand Terlaris',
        'benefits' => 'Keuntungan Belanja di Inofarma',
        'nearest' => 'Apotek Inofarma Terdekat',
        'testimonials' => 'Testimoni Sobat Ino',
    ];

    /**
     * @var array{
     *     headings: array<string, string>,
     *     hero: list<array{image: string, href: string, alt: string}>,
     *     promo: list<array{image: string, href: string, alt: string}>,
     *     bottom: list<array{image: string, href: string, alt: string}>,
     *     allProductsIcon: string,
     *     brands: list<array{name: string, image: string}>,
     *     benefits: list<array{label: string, image: string}>,
     *     testimonials: list<array{name: string, branch: string, image: string, quote: string}>,
     *     texts: list<array{title: string, body: string}>,
     *     contactTitle: string,
     *     contactIntro: string,
     *     contacts: list<array{icon: string, title: string, lines: string}>,
     * }
     */
    public const DEFAULTS = [
        'headings' => self::HEADINGS,
        'hero' => [
            ['image' => '/media/images/hero/coming-soon.webp', 'href' => '/shop', 'alt' => 'Coming soon! Website masih dalam persiapan, stay tuned'],
            ['image' => '/media/images/hero/hero-banner-1.jpg', 'href' => '/shop', 'alt' => 'Lebih hemat, lebih lengkap, belanja produk kesehatan di Apotek Inofarma'],
            ['image' => '/media/images/hero/hero-banner-2.jpg', 'href' => '/signup', 'alt' => 'Gabung Sobat Ino, daftar gratis dan dapatkan harga spesial'],
            ['image' => '/media/images/hero/hero-banner-3.jpg', 'href' => '/cabang-kami', 'alt' => 'Inofarma siap antar 24/7'],
        ],
        'promo' => [
            ['image' => '/media/images/promo/sehat-ga-mesti-mahal.png', 'href' => '/signup', 'alt' => 'Sehat ga mesti mahal, ayo daftar member Sobat Ino'],
            ['image' => '/media/images/promo/harga-sobat-produk-lengkap.png', 'href' => '/signup', 'alt' => 'Harga Sobat, Produk Lengkap, gabung Sobat Ino sekarang'],
        ],
        'bottom' => [
            ['image' => '/media/images/promo/pengiriman-instan-24-jam.png', 'href' => '/cabang-kami', 'alt' => 'Pengiriman instan dengan layanan antar 24 jam'],
            ['image' => '/media/images/promo/temukan-cabang-terdekat.png', 'href' => '/cabang-kami', 'alt' => 'Temukan cabang Inofarma terdekat'],
            ['image' => '/media/images/promo/selalu-lebih-hemat.png', 'href' => '/shop', 'alt' => 'Selalu lebih hemat, lebih lengkap'],
        ],
        'allProductsIcon' => '/media/images/categories/semua-produk.png',
        'brands' => [
            ['name' => 'Sido Muncul', 'image' => '/media/images/brands/sido-muncul.png'],
            ['name' => 'Stimuno', 'image' => '/media/images/brands/stimuno.png'],
            ['name' => 'Bayer', 'image' => '/media/images/brands/bayer.png'],
            ['name' => 'Bisolvon', 'image' => '/media/images/brands/bisolvon.png'],
            ['name' => 'Vicks', 'image' => '/media/images/brands/vicks.png'],
            ['name' => 'My Baby', 'image' => '/media/images/brands/my-baby.png'],
            ['name' => 'Panadol', 'image' => '/media/images/brands/panadol.png'],
            ['name' => 'Cap Lang', 'image' => '/media/images/brands/cap-lang.png'],
            ['name' => 'Counterpain', 'image' => '/media/images/brands/counterpain.png'],
        ],
        'benefits' => [
            ['label' => 'Produk Kesehatan Termurah', 'image' => '/media/images/benefits/produk-kesehatan-termurah.png'],
            ['label' => 'Hemat Setiap Hari', 'image' => '/media/images/benefits/hemat-setiap-hari.png'],
            ['label' => 'Produk Lengkap', 'image' => '/media/images/benefits/produk-lengkap.png'],
            ['label' => 'Apotek Buka 24 Jam', 'image' => '/media/images/benefits/apotek-buka-24-jam.png'],
            ['label' => 'Layanan Antar 24 Jam', 'image' => '/media/images/benefits/layanan-antar-24-jam.png'],
            ['label' => 'Mudah Dijangkau', 'image' => '/media/images/benefits/mudah-dijangkau.png'],
            ['label' => 'Konsultasi Gratis', 'image' => '/media/images/benefits/konsultasi-gratis.png'],
            ['label' => 'Benefit Sobat Ino', 'image' => '/media/images/benefits/benefit-sobat-ino.png'],
            ['label' => 'Belanja Praktis', 'image' => '/media/images/benefits/belanja-praktis.png'],
        ],
        'testimonials' => [
            [
                'name' => 'Satara Jufry',
                'branch' => 'Apotek Inofarma Jengki',
                'image' => '/media/images/testimonials/sobat-ino-2.png',
                'quote' => 'baru kali ini review apotek karena sangat impressed dengan pelayanannya! :) btw, saya beli online via chat customer care di WhatsApp.',
            ],
            [
                'name' => 'Irfan',
                'branch' => 'Apotek Inofarma Kayu Manis',
                'image' => '/media/images/testimonials/sobat-ino-1.png',
                'quote' => 'Obat-obatannya lengkap, Banyak promo & Harganya sudah murah ditambah diskon lagi klo kita daftar jadi member, Gratis lagi cuma sebutkan nama dan nomer handphone, Cocok bgt buat langganan yg cari obat dengan resep dokter atau tanpa resep dokter.',
            ],
            [
                'name' => 'Jia Nur',
                'branch' => 'Apotek Inofarma Pisangan Lama',
                'image' => '/media/images/testimonials/sobat-ino-2.png',
                'quote' => 'Pelayanan ramah dan cepat, farmasinya informatif dan membatu menjelaskan obat dengan jelas, stock juga cukup lengkap, bersih, dan sangat memudahkan saat butuh obat mendesak👍🏻',
            ],
            [
                'name' => 'Ismi Khairani',
                'branch' => 'Apotek Inofarma Kalisari',
                'image' => '/media/images/testimonials/sobat-ino-1.png',
                'quote' => 'Suka banget belanja obat disini, pelayanannya ramah, apalagi kakak nya yang agak muda itu, ramah banget bintang 7 deh kalo bisa.',
            ],
        ],
        'texts' => [
            [
                'title' => 'Apotek Inofarma: Solusi Kesehatan Terhemat dan Terlengkap',
                'body' => 'Apotek Inofarma hadir sebagai solusi kesehatan terpercaya dengan jaringan apotek yang mudah dijangkau dan layanan yang selalu mengutamakan kebutuhan pelanggan. Kami menyediakan produk kesehatan terlengkap, vitamin, suplemen, dan perawatan diri dengan harga terhemat dan layanan yang bersahabat. Apotek Inofarma juga memberikan konsultasi obat gratis dan cek kesehatan dasar dengan apoteker profesional. Kami berkomitmen untuk selalu menghadirkan program dan penawaran menarik, siap membantu Anda menjaga kesehatan dengan cara yang mudah, aman, dan praktis.',
            ],
            [
                'title' => 'Belanja Obat Praktis dengan Layanan Antar 24 Jam',
                'body' => "Belanja produk kesehatan kini lebih mudah bersama Apotek Inofarma. Dengan layanan apotek buka 24 jam dan antar obat gratis*, kami siap memenuhi kebutuhan kesehatan Anda kapan saja, di mana saja. Pemesanan obat dapat dilakukan dengan mudah melalui WhatsApp, sehingga Anda dapat menerima obat dengan cepat tanpa repot. Apotek Inofarma juga menyediakan tes darah cepat (asam urat, gula darah, kolesterol total) dengan harga terjangkau untuk membantu Anda memantau kesehatan secara berkala. Selain itu, pelanggan setia Sobat Ino dapat menikmati berbagai benefit member eksklusif (untuk pembelian di outlet). Semua produk dijamin asli, terdaftar BPOM, dan berkualitas tinggi. Bersama Apotek Inofarma, kesehatan selalu lebih dekat, hemat, dan bersahabat.\n\n*S&K berlaku",
            ],
        ],
        'contactTitle' => 'Hubungi Inofarma Kapan Saja',
        'contactIntro' => 'Hubungi kami untuk pertanyaan seputar produk, pesanan, maupun layanan dari Apotek Inofarma. Kami selalu siap untuk membantu kebutuhan kesehatan Anda.',
        'contacts' => [
            [
                'icon' => 'phone',
                'title' => 'Layanan Pelanggan Inofarma',
                'lines' => "Email: cs@inofarma.com\nWhatsApp: 0812-1401-8964\nPhone: (021) 50959952",
            ],
            [
                'icon' => 'email',
                'title' => 'Layanan Pengaduan Konsumen',
                'lines' => "Direktorat Jenderal Perlindungan Konsumen Tertib Niaga\nKementerian Perdagangan RI\nWhatsApp: 0853-1111-1010",
            ],
        ],
    ];

    /**
     * Saved content over the defaults, section by section, in the shape the admin form edits.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $saved = array_intersect_key(ContentStore::read(self::KEY), self::DEFAULTS);

        // Headings are merged one by one, so a heading added later still gets its default.
        $saved['headings'] = array_replace(self::HEADINGS, array_intersect_key((array) ($saved['headings'] ?? []), self::HEADINGS));

        return array_replace(self::DEFAULTS, $saved);
    }

    /**
     * @param  array<string, mixed>  $content  the admin form's state; unknown keys are dropped
     */
    public static function save(array $content): void
    {
        $content = array_intersect_key($content, self::DEFAULTS);

        if (isset($content['headings'])) {
            $content['headings'] = array_map(fn ($heading) => trim((string) $heading), array_intersect_key((array) $content['headings'], self::HEADINGS));
        }

        foreach (array_diff_key($content, ['headings' => true]) as $section => $value) {
            $content[$section] = is_array($value)
                ? array_values(array_map(fn ($item) => self::withImageUrl((array) $item), $value))
                : trim((string) $value);
        }

        if (filled($content['allProductsIcon'] ?? null)) {
            $content['allProductsIcon'] = self::imageUrl($content['allProductsIcon']);
        }

        ContentStore::write(self::KEY, $content);
    }

    /**
     * What the home page renders. Texts and contact lines are split into
     * paragraphs and lines here, so the page only has to loop.
     *
     * @return array<string, mixed>
     */
    public static function forStorefront(): array
    {
        $content = self::all();
        $withImage = fn (array $items) => array_values(array_filter($items, fn ($item) => filled($item['image'] ?? null)));

        return [
            'headings' => collect($content['headings'])->map(fn (string $heading, string $key) => $heading !== '' ? $heading : self::HEADINGS[$key])->all(),
            'hero' => $withImage($content['hero']),
            'promo' => $withImage($content['promo']),
            'bottom' => $withImage($content['bottom']),
            'allProductsIcon' => $content['allProductsIcon'] ?: self::DEFAULTS['allProductsIcon'],
            'brands' => $withImage($content['brands']),
            'benefits' => $withImage($content['benefits']),
            'testimonials' => array_values($content['testimonials']),
            'texts' => array_map(fn (array $text) => [
                'title' => $text['title'],
                'paragraphs' => ContentStore::paragraphs((string) $text['body']),
            ], $content['texts']),
            'contactTitle' => $content['contactTitle'],
            'contactIntro' => $content['contactIntro'],
            'contacts' => array_map(fn (array $contact) => [
                'icon' => in_array($contact['icon'] ?? null, self::CONTACT_ICONS, true) ? $contact['icon'] : 'phone',
                'title' => $contact['title'],
                'lines' => ContentStore::lines((string) $contact['lines']),
            ], $content['contacts']),
        ];
    }

    /**
     * Sets a category's homepage icon. `$image` is what the upload field holds.
     */
    public static function setCategoryIcon(Category $category, string $image): void
    {
        $category->update(['image_path' => self::imageUrl($image)]);
    }

    /**
     * A shipped `/media/...` path or a full URL stays as it is; a file just
     * uploaded to the uploads disk becomes its public URL.
     */
    public static function imageUrl(string $image): string
    {
        if (str_starts_with($image, '/') || Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        $url = MediaLibrary::disk()->url($image);

        return str_starts_with($url, '/') ? url($url) : $url;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private static function withImageUrl(array $item): array
    {
        if (filled($item['image'] ?? null)) {
            $item['image'] = self::imageUrl((string) $item['image']);
        }

        return $item;
    }
}
