import { Link } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import Icon from '@/Components/BeShop/Icon';
import TabBar from '@/Components/BeShop/TabBar';
import useBeShopUser from '@/Components/BeShop/useBeShopUser';
import { asset } from '@/Components/BeShop/data';

const menu = [
    { label: 'Edit profile', icon: 'user', href: '/ui/edit-profile' },
    { label: 'Payment method', icon: 'card', href: '/ui/payment-methods' },
    { label: 'My address', icon: 'pin', href: '/ui/my-address' },
    { label: 'My promocodes', icon: 'promo', href: '/ui/my-promocodes' },
    { label: 'Order history', icon: 'file', href: '/ui/order-history' },
    { label: 'Shipping & payment info', icon: 'info', href: '/ui/shipping-info' },
    { label: 'FAQ', icon: 'help', href: '/ui/faq' },
];

export default function Profile() {
    const user = useBeShopUser();

    return (
        <MobileLayout title="Profile" footer={<TabBar active="profile" />}>
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

                    <span className="flex-1 text-[13px] text-brand">Sign out</span>

                    <Icon name="chevronRight" size={14} className="text-[#cccccc]" />
                </Link>
            </div>
        </MobileLayout>
    );
}
