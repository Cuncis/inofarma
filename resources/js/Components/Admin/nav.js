/**
 * Admin sidebar navigation.
 *
 * Mirrors the source template's menu, minus the toolkit showcase sections
 * (Base UI, Charts, Forms, Tables, Icons, Maps, Widgets) which were theme
 * documentation rather than screens of this application.
 *
 * `href` is an admin route path; a group with `children` has no href of its own.
 *
 * @type {{ title: string, items: NavItem[] }[]}
 *
 * @typedef {object} NavItem
 * @property {string} label
 * @property {string} icon
 * @property {string} [href]
 * @property {{ label: string, href: string }[]} [children]
 * @property {{ text: string, tone: string }} [badge]
 */
export const navSections = [
    {
        title: 'Umum',
        items: [
            { label: 'Dasbor', icon: 'solar:widget-5-bold-duotone', href: '/admin' },
            {
                label: 'Produk',
                icon: 'solar:t-shirt-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/produk' },
                    { label: 'Grid', href: '/admin/produk/grid' },
                    { label: 'Detail', href: '/admin/produk/detail' },
                    { label: 'Ubah', href: '/admin/produk/ubah' },
                    { label: 'Tambah', href: '/admin/produk/tambah' },
                ],
            },
            {
                label: 'Kategori',
                icon: 'solar:clipboard-list-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/kategori' },
                    { label: 'Detail', href: '/admin/kategori/detail' },
                    { label: 'Ubah', href: '/admin/kategori/ubah' },
                    { label: 'Tambah', href: '/admin/kategori/tambah' },
                ],
            },
            {
                label: 'Inventaris',
                icon: 'solar:box-bold-duotone',
                children: [
                    { label: 'Gudang', href: '/admin/inventaris/gudang' },
                    { label: 'Pesanan Masuk', href: '/admin/inventaris/pesanan-masuk' },
                ],
            },
            {
                label: 'Pesanan',
                icon: 'solar:bag-smile-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/pesanan' },
                    { label: 'Detail', href: '/admin/pesanan/detail' },
                    { label: 'Keranjang', href: '/admin/pesanan/keranjang' },
                    { label: 'Checkout', href: '/admin/pesanan/checkout' },
                ],
            },
            {
                label: 'Pembelian',
                icon: 'solar:card-send-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/pembelian' },
                    { label: 'Order', href: '/admin/pembelian/order' },
                    { label: 'Retur', href: '/admin/pembelian/retur' },
                ],
            },
            {
                label: 'Atribut',
                icon: 'solar:confetti-minimalistic-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/atribut' },
                    { label: 'Ubah', href: '/admin/atribut/ubah' },
                    { label: 'Tambah', href: '/admin/atribut/tambah' },
                ],
            },
            {
                label: 'Faktur',
                icon: 'solar:bill-list-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/faktur' },
                    { label: 'Detail', href: '/admin/faktur/detail' },
                    { label: 'Ubah', href: '/admin/faktur/ubah' },
                    { label: 'Tambah', href: '/admin/faktur/tambah' },
                ],
            },
            { label: 'Pengaturan', icon: 'solar:settings-bold-duotone', href: '/admin/pengaturan' },
            { label: 'Profil', icon: 'solar:user-circle-bold-duotone', href: '/admin/profil' },
            {
                label: 'Peran',
                icon: 'solar:user-speak-rounded-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/peran' },
                    { label: 'Ubah', href: '/admin/peran/ubah' },
                    { label: 'Tambah', href: '/admin/peran/tambah' },
                ],
            },
            {
                label: 'Hak Akses',
                icon: 'solar:checklist-minimalistic-bold-duotone',
                href: '/admin/hak-akses',
            },
            {
                label: 'Pelanggan',
                icon: 'solar:users-group-two-rounded-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/pelanggan' },
                    { label: 'Detail', href: '/admin/pelanggan/detail' },
                    { label: 'Ubah', href: '/admin/pelanggan/ubah' },
                    { label: 'Tambah', href: '/admin/pelanggan/tambah' },
                ],
            },
            {
                label: 'Penjual',
                icon: 'solar:shop-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/penjual' },
                    { label: 'Detail', href: '/admin/penjual/detail' },
                    { label: 'Ubah', href: '/admin/penjual/ubah' },
                    { label: 'Tambah', href: '/admin/penjual/tambah' },
                ],
            },
            {
                label: 'Kupon',
                icon: 'solar:leaf-bold-duotone',
                children: [
                    { label: 'Daftar', href: '/admin/kupon' },
                    { label: 'Tambah', href: '/admin/kupon/tambah' },
                ],
            },
            { label: 'Ulasan', icon: 'solar:chat-square-like-bold-duotone', href: '/admin/ulasan' },
        ],
    },
    {
        title: 'Aplikasi',
        items: [
            { label: 'Chat', icon: 'solar:chat-round-bold-duotone', href: '/admin/chat' },
            { label: 'Email', icon: 'solar:mailbox-bold-duotone', href: '/admin/email' },
            { label: 'Kalender', icon: 'solar:calendar-bold-duotone', href: '/admin/kalender' },
            { label: 'Todo', icon: 'solar:checklist-bold-duotone', href: '/admin/todo' },
        ],
    },
    {
        title: 'Lainnya',
        items: [
            { label: 'Pusat Bantuan', icon: 'solar:help-bold-duotone', href: '/admin/bantuan' },
            { label: 'FAQ', icon: 'solar:question-circle-bold-duotone', href: '/admin/faq' },
            {
                label: 'Kebijakan Privasi',
                icon: 'solar:document-text-bold-duotone',
                href: '/admin/kebijakan-privasi',
            },
            {
                label: 'Halaman',
                icon: 'solar:gift-bold-duotone',
                children: [
                    { label: 'Selamat Datang', href: '/admin/halaman/selamat-datang' },
                    { label: 'Segera Hadir', href: '/admin/halaman/segera-hadir' },
                    { label: 'Linimasa', href: '/admin/halaman/linimasa' },
                    { label: 'Harga', href: '/admin/halaman/harga' },
                    { label: 'Pemeliharaan', href: '/admin/halaman/pemeliharaan' },
                    { label: 'Error 404', href: '/admin/halaman/404' },
                    { label: 'Error 404 (alt)', href: '/admin/halaman/404-alt' },
                ],
            },
            {
                label: 'Autentikasi',
                icon: 'solar:lock-keyhole-bold-duotone',
                children: [
                    { label: 'Masuk', href: '/admin/auth/masuk' },
                    { label: 'Daftar', href: '/admin/auth/daftar' },
                    { label: 'Atur Ulang Sandi', href: '/admin/auth/atur-ulang-sandi' },
                    { label: 'Kunci Layar', href: '/admin/auth/kunci-layar' },
                ],
            },
        ],
    },
];

/** Flat list of every navigable admin path, in menu order. */
export const allNavLinks = navSections.flatMap((section) =>
    section.items.flatMap((item) =>
        item.children
            ? item.children.map((child) => ({ ...child, parent: item.label }))
            : [{ label: item.label, href: item.href }],
    ),
);
