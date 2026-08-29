import { usePage } from '@inertiajs/react';

/**
 * The catalogue, read from the server.
 *
 * This file used to hold the products themselves. It no longer does: the shop
 * and the admin both read the `products` table, so an edit in the admin shows
 * up in the shop on the next request. What is left here is the shape of that
 * data and the derived views the screens ask for.
 *
 * `catalog` is a shared Inertia prop — see `HandleInertiaRequests::share()`.
 *
 * @typedef {object} CatalogProduct
 * @property {string} id          SKU, e.g. PRD-001.
 * @property {string} slug
 * @property {string} name
 * @property {string} category
 * @property {string} image       The primary photo — grids/strips/cards use this.
 * @property {{path: string, alt: string}[]} images  Every photo, in display order — the product-detail gallery.
 * @property {number} price       Current selling price, in rupiah.
 * @property {number} [oldPrice]  Struck-through price when the item is discounted.
 * @property {number} stock       Across every branch.
 * @property {number} sold
 * @property {string} rating
 * @property {string} status      Availability: Tersedia | Stok Menipis | Habis.
 * @property {string} unit
 * @property {string[]} variants
 * @property {boolean} prescription
 * @property {string} blurb
 * @property {string} [drugClass]      Non-Obat | Bebas | Bebas Terbatas | Keras.
 * @property {boolean} needsWarningLabel  True for Bebas Terbatas — P1-P6 must show.
 * @property {string} [nie]            NIE BPOM registration number.
 * @property {string} [composition]
 * @property {string} [indication]
 * @property {string} [dosage]
 * @property {string} [sideEffects]
 * @property {string} [warning]        P1-P6 warning text for Bebas Terbatas.
 * @property {string} [manufacturer]
 * @property {number} [maxQtyPerOrder]
 * @property {string} [storage]        Suhu Ruang | Sejuk (15-25°C) | Dingin/Kulkas (2-8°C).
 *
 * @typedef {object} CatalogCategory
 * @property {string} name
 * @property {string} slug
 * @property {string} image
 * @property {string} status
 * @property {number} products
 *
 * @typedef {object} Catalog
 * @property {CatalogProduct[]} products
 * @property {CatalogCategory[]} categories
 */

const EMPTY = { products: [], categories: [] };

/**
 * The catalogue for the current page.
 *
 * Falls back to empty rather than throwing, so a screen that renders before the
 * prop exists (or in a test that stubs the page) degrades to "nothing to show"
 * instead of a blank error.
 *
 * @returns {Catalog}
 */
export function useCatalog() {
    return usePage().props.catalog ?? EMPTY;
}

/**
 * Look a product up by SKU, falling back to the first so a detail screen always
 * has something to render.
 *
 * @param {CatalogProduct[]} products
 * @param {string} [id]
 * @returns {CatalogProduct | undefined}
 */
export function findProduct(products, id) {
    return products.find((product) => product.id === id) ?? products[0];
}

/**
 * @param {CatalogProduct[]} products
 * @param {string} name
 * @returns {CatalogProduct[]}
 */
export function productsInCategory(products, name) {
    return products.filter((product) => product.category === name);
}

/**
 * Best sellers first.
 *
 * @param {CatalogProduct[]} products
 * @returns {CatalogProduct[]}
 */
export function bestSellers(products) {
    return [...products].sort((a, b) => b.sold - a.sold);
}

/**
 * Everything currently discounted.
 *
 * @param {CatalogProduct[]} products
 * @returns {CatalogProduct[]}
 */
export function discounted(products) {
    return products.filter((product) => product.oldPrice);
}
