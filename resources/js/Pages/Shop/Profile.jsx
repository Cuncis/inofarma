import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Icon from '@/Components/Shop/Icon';
import IconLink from '@/Components/Shop/IconLink';
import TabBar from '@/Components/Shop/TabBar';
import useShopUser from '@/Components/Shop/useShopUser';
import { asset } from '@/Components/Shop/data';

const menu = [
    { label: 'Ubah profil', icon: 'user', href: '/ui/edit-profile' },
    { label: 'Metode pembayaran', icon: 'card', href: '/ui/payment-methods' },
    { label: 'Alamat saya', icon: 'pin', href: '/ui/my-address' },
    { label: 'Cabang kami', icon: 'pin', href: '/ui/cabang-kami' },
    { label: 'Kode promo saya', icon: 'promo', href: '/ui/my-promocodes' },
    { label: 'Riwayat pesanan', icon: 'file', href: '/ui/order-history' },
    { label: 'Info pengiriman & pembayaran', icon: 'info', href: '/ui/shipping-info' },
    { label: 'Kebijakan pengembalian dana', icon: 'info', href: '/ui/kebijakan-pengembalian-dana' },
    { label: 'Syarat & ketentuan', icon: 'info', href: '/ui/syarat-ketentuan' },
    { label: 'Kebijakan privasi', icon: 'info', href: '/ui/kebijakan-privasi' },
    { label: 'Privasi saya', icon: 'user', href: '/ui/privasi-saya' },
    { label: 'Tentang kami', icon: 'info', href: '/ui/tentang-kami' },
    { label: 'FAQ', icon: 'help', href: '/ui/faq' },
];

export default function Profile() {
    const user = useShopUser();

    return (
        <MobileLayout
            title="Profil"
            header={
                <AppBar
                    title="Profil"
                    tone="brand"
                    actions={<IconLink name="history" href="/ui/order-history" label="Riwayat transaksi" />}
                />
            }
            footer={<TabBar active="profile" />}
        >
            <div className="flex-1 overflow-y-auto p-4 pb-[70px]">
                <div className="flex flex-col items-center pb-[18px] pt-5">
                    <div className="mb-3 h-[88px] w-[88px] overflow-hidden rounded-full border-4 border-brand">
                        <img
                            src={asset.user('01')}
                            alt={user.name}
                            className="h-full w-full object-cover"
                        />
                    </div>

                    <div className="mb-[3px] font-display text-[17px]">{user.name}</div>
                    <div className="text-xs text-faint">{user.email}</div>
                </div>

                {menu.map((item) => (
                    <Link
                        key={item.label}
                        href={item.href}
                        className="mb-[7px] flex items-center gap-3 border border-line px-3.5 py-[13px]"
                    >
                        <span className="text-ink">
                            <Icon name={item.icon} size={19} />
                        </span>

                        <span className="flex-1 text-[13px] text-ink">{item.label}</span>

                        <Icon name="chevronRight" size={14} className="text-[#cccccc]" />
                    </Link>
                ))}

                <Link
                    href="/ui/signout"
                    method="post"
                    as="button"
                    className="mb-[7px] flex w-full items-center gap-3 border border-line px-3.5 py-[13px] text-left"
                >
                    <span className="text-brand">
                        <Icon name="logout" size={19} />
                    </span>

                    <span className="flex-1 text-[13px] text-brand">Keluar</span>

                    <Icon name="chevronRight" size={14} className="text-[#cccccc]" />
                </Link>
            </div>
        </MobileLayout>
    );
}
