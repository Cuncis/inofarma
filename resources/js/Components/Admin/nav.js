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
            { label: 'Produk', icon: 'solar:t-shirt-bold-duotone', href: '/admin/produk' },
            { label: 'Kategori', icon: 'solar:clipboard-list-bold-duotone', href: '/admin/kategori' },
            { label: 'Cabang', icon: 'solar:shop-2-bold-duotone', href: '/admin/cabang' },
            {
                label: 'Inventaris',
                icon: 'solar:box-bold-duotone',
                children: [
                    { label: 'Stok per Cabang', href: '/admin/inventaris/stok' },
                    { label: 'Matriks Stok', href: '/admin/inventaris/matriks' },
                    { label: 'Transfer Stok', href: '/admin/inventaris/transfer' },
                ],
            },
            { label: 'Pesanan', icon: 'solar:bag-smile-bold-duotone', href: '/admin/pesanan' },
            { label: 'Pengambilan', icon: 'solar:qr-code-bold-duotone', href: '/admin/pengambilan' },
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
                href: '/admin/atribut',
            },
            { label: 'Faktur', icon: 'solar:bill-list-bold-duotone', href: '/admin/faktur' },
            {
                label: 'Rekonsiliasi',
                icon: 'solar:card-transfer-bold-duotone',
                href: '/admin/rekonsiliasi',
            },
            { label: 'Pengaturan', icon: 'solar:settings-bold-duotone', href: '/admin/pengaturan' },
            { label: 'Profil', icon: 'solar:user-circle-bold-duotone', href: '/admin/profil' },
            { label: 'Staf Admin', icon: 'solar:user-id-bold-duotone', href: '/admin/staf' },
            { label: 'Peran', icon: 'solar:user-speak-rounded-bold-duotone', href: '/admin/peran' },
            {
                label: 'Hak Akses',
                icon: 'solar:checklist-minimalistic-bold-duotone',
                href: '/admin/hak-akses',
            },
            { label: 'Keamanan', icon: 'solar:shield-check-bold-duotone', href: '/admin/keamanan' },
            {
                label: 'Pelanggan',
                icon: 'solar:users-group-two-rounded-bold-duotone',
                href: '/admin/pelanggan',
            },
            { label: 'Pemasok', icon: 'solar:shop-bold-duotone', href: '/admin/pemasok' },
            { label: 'Kupon', icon: 'solar:leaf-bold-duotone', href: '/admin/kupon' },
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
