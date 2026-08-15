import { catalogProducts, categoriesWithCounts } from '@/lib/catalog';
import { customers, invoices, orders, sellers } from './data';
import { allNavLinks } from './nav';

/**
 * Everything the admin's global search can find.
 *
 * Built once at module load from the same fixtures the screens render, so a
 * result always corresponds to a page that exists.
 *
 * @typedef {object} SearchEntry
 * @property {string} group   Heading the result is listed under.
 * @property {string} label   Primary line.
 * @property {string} [meta]  Secondary line.
 * @property {string} href    Where selecting it navigates.
 * @property {string} icon
 *
 * @type {SearchEntry[]}
 */
export const searchIndex = [
    ...catalogProducts.map((product) => ({
        group: 'Produk',
        label: product.name,
        meta: `${product.id} · ${product.category}`,
        href: '/admin/produk/detail',
        icon: 'solar:box-bold-duotone',
    })),

    ...categoriesWithCounts.map((category) => ({
        group: 'Kategori',
        label: category.name,
        meta: `${category.products} produk`,
        href: '/admin/kategori/detail',
        icon: 'solar:clipboard-list-bold-duotone',
    })),

    ...orders.map((order) => ({
        group: 'Pesanan',
        label: order.id,
        meta: `${order.customer} · ${order.status}`,
        href: '/admin/pesanan/detail',
        icon: 'solar:bag-smile-bold-duotone',
    })),

    ...customers.map((customer) => ({
        group: 'Pelanggan',
        label: customer.name,
        meta: customer.email,
        href: '/admin/pelanggan/detail',
        icon: 'solar:users-group-two-rounded-bold-duotone',
    })),

    ...sellers.map((seller) => ({
        group: 'Penjual',
        label: seller.name,
        meta: `${seller.owner} · ${seller.city}`,
        href: '/admin/penjual/detail',
        icon: 'solar:shop-bold-duotone',
    })),

    ...invoices.map((invoice) => ({
        group: 'Faktur',
        label: invoice.number,
        meta: `${invoice.customer} · ${invoice.status}`,
        href: '/admin/faktur/detail',
        icon: 'solar:bill-list-bold-duotone',
    })),

    ...allNavLinks.map((link) => ({
        group: 'Halaman',
        label: link.parent ? `${link.parent} — ${link.label}` : link.label,
        meta: link.href,
        href: link.href,
        icon: 'solar:file-broken',
    })),
];

/**
 * Rank matches for a query.
 *
 * Entries whose label starts with the query outrank ones that merely contain
 * it, which keeps exact-ish matches at the top where the keyboard lands first.
 *
 * @param {string} query
 * @param {number} [limit]
 * @returns {SearchEntry[]}
 */
export function searchAdmin(query, limit = 8) {
    const needle = query.trim().toLowerCase();

    if (! needle) {
        return [];
    }

    return searchIndex
        .map((entry) => {
            const label = entry.label.toLowerCase();
            const meta = (entry.meta ?? '').toLowerCase();

            if (label.startsWith(needle)) {
                return { entry, score: 0 };
            }

            if (label.includes(needle)) {
                return { entry, score: 1 };
            }

            if (meta.includes(needle)) {
                return { entry, score: 2 };
            }

            return null;
        })
        .filter(Boolean)
        .sort((a, b) => a.score - b.score)
        .slice(0, limit)
        .map((hit) => hit.entry);
}
