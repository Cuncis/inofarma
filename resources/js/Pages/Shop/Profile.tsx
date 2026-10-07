import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Icon, { type IconName } from '@/Components/Shop/Icon';
import IconLink from '@/Components/Shop/IconLink';
import TabBar from '@/Components/Shop/TabBar';
import useShopUser from '@/Components/Shop/useShopUser';
import { asset } from '@/Components/Shop/data';

const menu: { label: string; icon: IconName; href: string }[] = [
    { label: 'Ubah profil', icon: 'user', href: '/edit-profile' },
    { label: 'Alamat saya', icon: 'pin', href: '/my-address' },
    { label: 'Cabang kami', icon: 'pin', href: '/cabang-kami' },
    { label: 'Kode promo saya', icon: 'promo', href: '/my-promocodes' },
    { label: 'Riwayat pesanan', icon: 'file', href: '/order-history' },
    { label: 'Info pengiriman & pembayaran', icon: 'info', href: '/shipping-info' },
    { label: 'Kebijakan pengembalian dana', icon: 'info', href: '/kebijakan-pengembalian-dana' },
    { label: 'Syarat & ketentuan', icon: 'info', href: '/syarat-ketentuan' },
    { label: 'Kebijakan privasi', icon: 'info', href: '/kebijakan-privasi' },
    { label: 'Privasi saya', icon: 'user', href: '/privasi-saya' },
    { label: 'Tentang kami', icon: 'info', href: 'http://info.inofarma.com/' },
    { label: 'FAQ', icon: 'help', href: '/faq' },
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
                    actions={<IconLink name="history" href="/order-history" label="Riwayat transaksi" />}
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

                {menu.map((item) => {
                    const rowClass = 'mb-[7px] flex items-center gap-3 border border-line bg-white px-3.5 py-[13px]';
                    const row = (
                        <>
                            <span className="text-ink">
                                <Icon name={item.icon} size={19} />
                            </span>

                            <span className="flex-1 text-[13px] text-ink">{item.label}</span>

                            <Icon name="chevronRight" size={14} className="text-[#cccccc]" />
                        </>
                    );

                    return item.href.startsWith('http') ? (
                        <a
                            key={item.label}
                            href={item.href}
                            target="_blank"
                            rel="noopener noreferrer"
                            className={rowClass}
                        >
                            {row}
                        </a>
                    ) : (
                        <Link key={item.label} href={item.href} className={rowClass}>
                            {row}
                        </Link>
                    );
                })}

                <Link
                    href="/signout"
                    method="post"
                    as="button"
                    className="mb-[7px] flex w-full items-center gap-3 border border-line bg-white px-3.5 py-[13px] text-left"
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
