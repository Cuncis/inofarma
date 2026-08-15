import { money } from '@/lib/format';

const assets = 'https://george-fx.github.io/beshop-data/assets';

export const asset = {
    product: (n) => `${assets}/products/${n}.jpg`,
    banner: (n) => `${assets}/banners/${n}.jpg`,
    category: (n) => `${assets}/categories/${n}.jpg`,
    user: (n) => `${assets}/users/${n}.jpg`,
    other: (n) => `${assets}/other/${n}.png`,
};

export { money };

/** @type {import('./ProductCard').Product[]} */
export const trendingProducts = [
    {
        name: 'Dress Maxi Sutra',
        image: asset.product('01'),
        price: money(1350000),
        rating: '4.9',
    },
    {
        name: 'Blus Motif Bunga',
        image: asset.product('02'),
        price: money(630000),
        rating: '4.7',
    },
    { name: 'Jaket Kulit', image: asset.product('03'), price: money(2175000), rating: '4.5' },
    { name: 'Dress Santai', image: asset.product('04'), price: money(570000), rating: '4.8' },
];

/** @type {import('./ProductCard').Product[]} */
export const newArrivals = [
    {
        name: 'Rok Denim',
        image: asset.product('05'),
        price: money(540000),
        oldPrice: money(780000),
        rating: '4.6',
    },
    { name: 'Kaos Kasual', image: asset.product('06'), price: money(375000), rating: '4.3' },
    {
        name: 'Celana Kulot',
        image: asset.product('07'),
        price: money(825000),
        oldPrice: money(1170000),
        rating: '4.6',
    },
    { name: 'Blazer Linen', image: asset.product('08'), price: money(1470000), rating: '4.8' },
];

/** @type {import('./ProductCard').Product[]} */
export const shopProducts = [
    {
        name: 'Dress Maxi Sutra',
        image: asset.product('01'),
        price: money(1350000),
        oldPrice: money(1485000),
        rating: '4.9',
    },
    {
        name: 'Blus Motif Bunga',
        image: asset.product('02'),
        price: money(630000),
        oldPrice: money(885000),
        rating: '4.7',
    },
    { name: 'Jaket Kulit', image: asset.product('03'), price: money(2175000), rating: '4.5' },
    { name: 'Dress Santai', image: asset.product('04'), price: money(570000), rating: '4.8' },
    { name: 'Kaos Kasual', image: asset.product('06'), price: money(375000), rating: '4.3' },
    {
        name: 'Celana Kulot',
        image: asset.product('07'),
        price: money(825000),
        oldPrice: money(1170000),
        rating: '4.6',
    },
];

/** @type {import('./ProductCard').Product[]} */
export const wishlistProducts = [
    {
        name: 'Dress Maxi Sutra',
        image: asset.product('01'),
        price: money(1350000),
        oldPrice: money(1485000),
        rating: '4.9',
    },
    {
        name: 'Blus Motif Bunga',
        image: asset.product('02'),
        price: money(630000),
        oldPrice: money(885000),
        rating: '4.7',
    },
    { name: 'Dress Santai', image: asset.product('04'), price: money(570000), rating: '4.8' },
    {
        name: 'Rok Denim',
        image: asset.product('05'),
        price: money(540000),
        oldPrice: money(780000),
        rating: '4.6',
    },
];

export const categories = [
    { name: 'Wanita', image: asset.category('01') },
    { name: 'Pria', image: asset.category('02') },
    { name: 'Anak', image: asset.category('03') },
    { name: 'Aksesori', image: asset.category('04') },
    { name: 'Rumah Tangga', image: asset.category('05') },
    { name: 'Kecantikan', image: asset.category('06') },
];

export const cartItems = [
    {
        name: 'Dress Maxi Sutra',
        image: asset.product('01'),
        amount: 1350000,
        quantity: 2,
        onSale: false,
    },
    {
        name: 'Jaket Kulit',
        image: asset.product('03'),
        amount: 2175000,
        quantity: 1,
        onSale: true,
    },
];

/** @type {import('./ReviewCard').Review[]} */
export const reviews = [
    {
        author: 'Sari W.',
        avatar: asset.user('02'),
        score: 5,
        age: '2 hari lalu',
        body: 'Suka banget sama dress ini! Bahannya adem, jatuhnya bagus, dan ukurannya pas.',
    },
    {
        author: 'Rizky A.',
        avatar: asset.user('03'),
        score: 4,
        age: '5 hari lalu',
        body: 'Kualitas bagus, tapi ukurannya agak kecil. Sebaiknya ambil satu nomor di atasnya.',
    },
    {
        author: 'Dinda P.',
        avatar: asset.user('04'),
        score: 5,
        age: '1 minggu lalu',
        body: 'Pengiriman cepat dan kemasannya rapi. Pasti belanja di sini lagi!',
    },
    {
        author: 'Bagas S.',
        avatar: asset.user('05'),
        score: 3,
        age: '2 minggu lalu',
        body: 'Produknya lumayan untuk harga segini. Warnanya sedikit beda dari foto.',
    },
];

export const addresses = [
    { title: 'Rumah', line: 'Jl. Kebon Jeruk Raya No. 27, Jakarta Barat 11530' },
    { title: 'Kantor', line: 'Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190' },
    { title: 'Rumah Orang Tua', line: 'Jl. Diponegoro No. 108, Bandung 40115' },
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

export const swatches = [
    '#222222',
    '#0900AA',
    '#24CE30',
    '#FE7900',
    '#c4a882',
    '#7ecac5',
    '#4a90e2',
    '#bd10e0',
];

export const sizes = ['XS', 'S', 'M', 'L', 'XL'];
