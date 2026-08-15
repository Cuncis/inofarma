import { catalogProducts, categoriesWithCounts } from '@/lib/catalog';
import { customers, invoices, orders, sellers } from './data';
import { allNavLinks } from './nav';

/**
 * Create/edit screens that are reached from inside a list page rather than the
 * sidebar. They left the nav when the CRUD groups were flattened, so they are
 * listed here to stay findable in search.
 *
 * @type {{ label: string, href: string }[]}
 */
const actionPages = [
    { label: 'Tambah Produk', href: '/admin/produk/tambah' },
    { label: 'Tambah Kategori', href: '/admin/kategori/tambah' },
    { label: 'Tambah Atribut', href: '/admin/atribut/tambah' },
    { label: 'Ubah Atribut', href: '/admin/atribut/ubah' },
    { label: 'Buat Faktur', href: '/admin/faktur/tambah' },
    { label: 'Ubah Faktur', href: '/admin/faktur/ubah' },
    { label: 'Tambah Peran', href: '/admin/peran/tambah' },
    { label: 'Ubah Peran', href: '/admin/peran/ubah' },
    { label: 'Tambah Pelanggan', href: '/admin/pelanggan/tambah' },
    { label: 'Tambah Penjual', href: '/admin/penjual/tambah' },
    { label: 'Tambah Kupon', href: '/admin/kupon/tambah' },
    { label: 'Buat Pesanan', href: '/admin/pesanan/tambah' },
    { label: 'Lupa Kata Sandi', href: '/admin/lupa-sandi' },
];

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
        href: `/admin/produk/${product.id}`,
        icon: 'solar:box-bold-duotone',
    })),

    ...categoriesWithCounts.map((category) => ({
        group: 'Kategori',
        label: category.name,
        meta: `${category.products} produk`,
        href: `/admin/kategori/${category.slug}`,
        icon: 'solar:clipboard-list-bold-duotone',
    })),

    ...orders.map((order) => ({
        group: 'Pesanan',
        label: `#${order.id}`,
        meta: `${order.customer} · ${order.status}`,
        href: `/admin/pesanan/${order.id}`,
        icon: 'solar:bag-smile-bold-duotone',
    })),

    ...customers.map((customer) => ({
        group: 'Pelanggan',
        label: customer.name,
        meta: customer.email,
        href: `/admin/pelanggan/${customer.id}`,
        icon: 'solar:users-group-two-rounded-bold-duotone',
    })),

    ...sellers.map((seller) => ({
        group: 'Penjual',
        label: seller.name,
        meta: `${seller.owner} · ${seller.city}`,
        href: `/admin/penjual/${seller.id}`,
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

    ...actionPages.map((page) => ({
        group: 'Tindakan',
        label: page.label,
        meta: page.href,
        href: page.href,
        icon: 'solar:pen-2-broken',
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
