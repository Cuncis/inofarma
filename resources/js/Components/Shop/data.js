import { money } from '@/lib/format';
import { bestSellers, discounted, findProduct, useCatalog } from '@/lib/catalog';
import { media } from '@/lib/media';

export { money, media, findProduct, useCatalog };

/**
 * Empty-state artwork, self-hosted under `public/media/images/other` (utang
 * teknis #3) instead of the original template author's GitHub Pages host —
 * the storefront no longer depends on a third party staying online.
 */
const illustrations = '/media/images/other';

/**
 * Decorative artwork for the empty/success screens. Product and people imagery
 * comes from `media` so the storefront and admin show the same pictures.
 */
export const asset = {
    other: (n) => `${illustrations}/${n}.png`,
    user: (n) => media.user(Number(n)),
    /**
     * The Inofarma wordmark, in the variant that reads on the background
     * it's placed on — dark navy/green artwork for a light background,
     * white artwork for a dark/blue one. Never use the light-background
     * file on a colored background or vice versa; the logo goes invisible.
     */
    logo: (tone) => `/media/images/logo-with-bg-${tone === 'blue' ? 'blue' : 'white'}.png`,
};

/**
 * Storefront view of the catalogue.
 *
 * Everything below is derived at render time from the shared `catalog` prop,
 * not from a fixture file — the shop sells exactly what the admin manages, so a
 * price changed in the admin is the price a shopper sees on the next request.
 *
 * The mobile screens want pre-formatted price strings, which is all `toTile`
 * does.
 */

/**
 * @param {import('@/lib/catalog').CatalogProduct} product
 * @returns {{ id: string, name: string, category: string, image: string, price: string, oldPrice?: string, rating: string }}
 */
const toTile = (product) => ({
    id: product.id,
    name: product.name,
    category: product.category,
    image: product.image,
    price: money(product.price),
    oldPrice: product.oldPrice ? money(product.oldPrice) : undefined,
    rating: product.rating,
});

/**
 * Every catalogue-derived list a storefront screen needs, for the current page.
 *
 * @returns {{
 *   products: import('@/lib/catalog').CatalogProduct[],
 *   recommended: object[],
 *   trendingProducts: object[],
 *   newArrivals: object[],
 *   shopProducts: object[],
 *   wishlistProducts: object[],
 *   topBrands: string[],
 *   categories: { name: string, image: string }[],
 *   filterCategories: string[],
 * }}
 */
export function useShopCatalog() {
    const { products, categories } = useCatalog();

    return {
        products,
        // "Rekomendasi Untukmu" — highest-rated first. A real recommendation
        // engine (purchase history, collaborative filtering) is out of scope
        // here; this is an honest, simple heuristic, not a placeholder pretending to be one.
        recommended: [...products]
            .sort((a, b) => parseFloat(b.rating) - parseFloat(a.rating))
            .slice(0, 6)
            .map(toTile),
        trendingProducts: bestSellers(products).slice(0, 6).map(toTile),
        newArrivals: products.slice(-6).map(toTile),
        shopProducts: products.map(toTile),
        wishlistProducts: discounted(products).slice(0, 4).map(toTile),
        // "Brand Terlaris" — real manufacturer names already on the product
        // record (Fase 4.2), deduplicated; no fixture brand list.
        topBrands: [...new Set(products.map((product) => product.manufacturer).filter(Boolean))].slice(0, 8),
        categories: categories.map((category) => ({
            name: category.name,
            image: category.image,
        })),
        filterCategories: categories.map((category) => category.name),
    };
}

/** @type {import('./ReviewCard').Review[]} */
export const reviews = [
    {
        author: 'Sari W.',
        avatar: media.user(2),
        score: 5,
        age: '2 hari lalu',
        body: 'Paracetamolnya asli dan segelnya utuh. Pengiriman cepat, sampai kurang dari sehari.',
    },
    {
        author: 'Rizky A.',
        avatar: media.user(3),
        score: 4,
        age: '5 hari lalu',
        body: 'Harga bersaing dan stok selalu ada. Sayang pilihan kemasan besarnya kadang kosong.',
    },
    {
        author: 'Dinda P.',
        avatar: media.user(4),
        score: 5,
        age: '1 minggu lalu',
        body: 'Dikemas rapi pakai bubble wrap, tanggal kedaluwarsanya juga masih lama. Puas!',
    },
    {
        author: 'Bagas S.',
        avatar: media.user(5),
        score: 3,
        age: '2 minggu lalu',
        body: 'Produknya bagus, tapi proses verifikasi resep untuk obat keras agak lama.',
    },
];

export const cards = ['**** **** **** 6644', '**** **** **** 8821'];

export const promocodes = [
    {
        name: 'Diskon Awal Bulan',
        discount: 'Diskon 15%',
        tone: 'text-success-deep',
        expires: 'Berlaku s/d 31 Des 2025',
        code: 'HEMAT15',
    },
    {
        name: 'Flash Sale',
        discount: 'Diskon 25%',
        tone: 'text-warning-deep',
        expires: 'Berlaku s/d 31 Agu 2025',
        code: 'KILAT25',
    },
    {
        name: 'Member VIP',
        discount: 'Diskon 40%',
        tone: 'text-brand',
        expires: 'Berlaku s/d 01 Jan 2026',
        code: 'VIP40',
    },
];

export const faqs = [
    {
        question: 'Apakah saya bisa memesan secara online?',
        answer: 'Tentu, Anda bisa memesan langsung lewat situs web atau aplikasi kami kapan saja.',
        open: true,
    },
    { question: 'Apakah tersedia produk perawatan kulit?', open: false },
    { question: 'Apakah ada layanan bungkus kado?', open: false },
    { question: 'Apakah produk Anda bebas uji coba hewan?', open: false },
    { question: 'Bagaimana cara melacak pesanan saya?', open: false },
];

/** Price brackets used by the filter screen, in rupiah. */
export const priceRanges = [
    { label: 'Di bawah Rp 25.000', max: 25000 },
    { label: 'Rp 25.000 - Rp 50.000', min: 25000, max: 50000 },
    { label: 'Rp 50.000 - Rp 100.000', min: 50000, max: 100000 },
    { label: 'Di atas Rp 100.000', min: 100000 },
];
