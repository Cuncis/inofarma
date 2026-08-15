/**
 * The product catalogue.
 *
 * Single source of truth for both the storefront and the admin — the shop grids,
 * the cart, the admin product tables and the category screens all read from here,
 * so a price or a name only ever needs changing in one place.
 *
 * Prices are plain rupiah numbers; format them with `money()` from `@/lib/format`
 * at the point of display rather than storing formatted strings.
 */

/** Images live under `public/media` because it is shared by both areas. */
export const media = {
    product: (n) => `/media/images/product/p-${n}.png`,
    category: (n) => `/media/images/small/img-${n}.jpg`,
    user: (n) => `/media/images/users/avatar-${n}.jpg`,
    seller: (n) => `/media/images/seller/${n}.svg`,
    brand: (n) => `/media/images/brands/${n}.png`,
};

/**
 * @typedef {object} CatalogProduct
 * @property {string} id
 * @property {string} name
 * @property {string} category
 * @property {string} image
 * @property {number} price       Current selling price, in rupiah.
 * @property {number} [oldPrice]  Struck-through price when the item is discounted.
 * @property {number} stock
 * @property {number} sold
 * @property {string} rating
 * @property {string} status
 * @property {string} unit        Base selling unit.
 * @property {string[]} variants  Pack sizes offered on the product page.
 * @property {boolean} prescription
 * @property {string} blurb
 */

/** @type {CatalogProduct[]} */
export const catalogProducts = [
    {
        id: 'PRD-001',
        name: 'Paracetamol 500mg',
        category: 'Obat Bebas',
        image: media.product(1),
        price: 12500,
        oldPrice: 15000,
        stock: 482,
        sold: 1240,
        rating: '4.8',
        status: 'Aktif',
        unit: 'Strip',
        variants: ['1 Strip', '5 Strip', '1 Box'],
        prescription: false,
        blurb: 'Meredakan demam dan nyeri ringan hingga sedang. Aman dikonsumsi setelah makan.',
    },
    {
        id: 'PRD-002',
        name: 'Amoxicillin 500mg',
        category: 'Obat Keras',
        image: media.product(2),
        price: 38000,
        stock: 126,
        sold: 860,
        rating: '4.6',
        status: 'Aktif',
        unit: 'Strip',
        variants: ['1 Strip', '3 Strip'],
        prescription: true,
        blurb: 'Antibiotik untuk infeksi bakteri. Wajib menyertakan resep dokter saat memesan.',
    },
    {
        id: 'PRD-003',
        name: 'Vitamin C 1000mg',
        category: 'Suplemen',
        image: media.product(3),
        price: 75000,
        oldPrice: 89000,
        stock: 0,
        sold: 2130,
        rating: '4.9',
        status: 'Habis',
        unit: 'Botol',
        variants: ['30 Tablet', '60 Tablet'],
        prescription: false,
        blurb: 'Membantu menjaga daya tahan tubuh. Dikonsumsi satu tablet per hari.',
    },
    {
        id: 'PRD-004',
        name: 'Masker Medis 3 Ply',
        category: 'Alat Kesehatan',
        image: media.product(4),
        price: 45000,
        stock: 1520,
        sold: 4210,
        rating: '4.7',
        status: 'Aktif',
        unit: 'Box',
        variants: ['1 Box (50 pcs)', '3 Box'],
        prescription: false,
        blurb: 'Masker tiga lapis dengan filter, nyaman dipakai seharian dan tidak mudah lepas.',
    },
    {
        id: 'PRD-005',
        name: 'Hand Sanitizer 500ml',
        category: 'Antiseptik',
        image: media.product(5),
        price: 32000,
        oldPrice: 40000,
        stock: 64,
        sold: 980,
        rating: '4.5',
        status: 'Stok Menipis',
        unit: 'Botol',
        variants: ['100ml', '500ml', '1 Liter'],
        prescription: false,
        blurb: 'Membunuh kuman tanpa perlu dibilas, dengan pelembap agar tangan tidak kering.',
    },
    {
        id: 'PRD-006',
        name: 'Termometer Digital',
        category: 'Alat Kesehatan',
        image: media.product(6),
        price: 125000,
        stock: 213,
        sold: 540,
        rating: '4.8',
        status: 'Aktif',
        unit: 'Pcs',
        variants: ['Standar', 'Infrared'],
        prescription: false,
        blurb: 'Pengukur suhu tubuh digital dengan hasil akurat dalam waktu sepuluh detik.',
    },
    {
        id: 'PRD-007',
        name: 'Vitamin D3 1000 IU',
        category: 'Suplemen',
        image: media.product(7),
        price: 68000,
        stock: 340,
        sold: 720,
        rating: '4.7',
        status: 'Aktif',
        unit: 'Botol',
        variants: ['30 Tablet', '90 Tablet'],
        prescription: false,
        blurb: 'Mendukung kesehatan tulang dan sistem imun, terutama bagi yang jarang terkena sinar matahari.',
    },
    {
        id: 'PRD-008',
        name: 'Minyak Kayu Putih 60ml',
        category: 'Obat Bebas',
        image: media.product(8),
        price: 24000,
        oldPrice: 30000,
        stock: 610,
        sold: 1580,
        rating: '4.6',
        status: 'Aktif',
        unit: 'Botol',
        variants: ['30ml', '60ml', '120ml'],
        prescription: false,
        blurb: 'Menghangatkan badan dan meredakan perut kembung. Cocok dibawa bepergian.',
    },
    {
        id: 'PRD-009',
        name: 'Plester Luka Isi 20',
        category: 'Alat Kesehatan',
        image: media.product(9),
        price: 18500,
        stock: 890,
        sold: 1120,
        rating: '4.4',
        status: 'Aktif',
        unit: 'Box',
        variants: ['Isi 20', 'Isi 50'],
        prescription: false,
        blurb: 'Plester elastis anti air untuk luka kecil, melekat kuat namun mudah dilepas.',
    },
    {
        id: 'PRD-010',
        name: 'Obat Batuk Sirup 100ml',
        category: 'Obat Bebas',
        image: media.product(10),
        price: 29500,
        oldPrice: 35000,
        stock: 275,
        sold: 940,
        rating: '4.5',
        status: 'Aktif',
        unit: 'Botol',
        variants: ['60ml', '100ml'],
        prescription: false,
        blurb: 'Meredakan batuk berdahak dan melegakan tenggorokan. Tersedia rasa jeruk.',
    },
    {
        id: 'PRD-011',
        name: 'Sunscreen SPF 50',
        category: 'Perawatan Kulit',
        image: media.product(11),
        price: 96000,
        stock: 158,
        sold: 660,
        rating: '4.8',
        status: 'Aktif',
        unit: 'Botol',
        variants: ['30ml', '50ml'],
        prescription: false,
        blurb: 'Perlindungan harian dari sinar UVA dan UVB, ringan dan tidak meninggalkan whitecast.',
    },
    {
        id: 'PRD-012',
        name: 'Alkohol Swab Isi 100',
        category: 'Antiseptik',
        image: media.product(12),
        price: 21000,
        stock: 430,
        sold: 810,
        rating: '4.3',
        status: 'Aktif',
        unit: 'Box',
        variants: ['Isi 100', 'Isi 200'],
        prescription: false,
        blurb: 'Tisu alkohol steril sekali pakai untuk membersihkan kulit sebelum penyuntikan.',
    },
];

/** @type {{ name: string, slug: string, image: string, status: string }[]} */
export const catalogCategories = [
    { name: 'Obat Bebas', slug: 'obat-bebas', image: media.category(1), status: 'Aktif' },
    { name: 'Obat Keras', slug: 'obat-keras', image: media.category(2), status: 'Aktif' },
    { name: 'Suplemen', slug: 'suplemen', image: media.category(3), status: 'Aktif' },
    { name: 'Alat Kesehatan', slug: 'alat-kesehatan', image: media.category(4), status: 'Aktif' },
    { name: 'Antiseptik', slug: 'antiseptik', image: media.category(6), status: 'Aktif' },
    { name: 'Perawatan Kulit', slug: 'perawatan-kulit', image: media.category(10), status: 'Nonaktif' },
];

/**
 * Look a product up by id, falling back to the first so a detail screen always
 * has something to render.
 *
 * @param {string} [id]
 * @returns {CatalogProduct}
 */
export function findProduct(id) {
    return catalogProducts.find((product) => product.id === id) ?? catalogProducts[0];
}

/**
 * Products in a category, newest first.
 *
 * @param {string} name
 * @returns {CatalogProduct[]}
 */
export function productsInCategory(name) {
    return catalogProducts.filter((product) => product.category === name);
}

/** Best sellers, for the storefront's trending rail and the admin dashboard. */
export const bestSellers = [...catalogProducts].sort((a, b) => b.sold - a.sold);

/** Everything currently discounted. */
export const discounted = catalogProducts.filter((product) => product.oldPrice);

/** Categories with their live product counts. */
export const categoriesWithCounts = catalogCategories.map((category) => ({
    ...category,
    products: productsInCategory(category.name).length,
}));
