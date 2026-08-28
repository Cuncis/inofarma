import { Link, router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Field from '@/Components/Shop/Field';
import Icon from '@/Components/Shop/Icon';
import useShopUser from '@/Components/Shop/useShopUser';
import { asset } from '@/Components/Shop/data';

/**
 * @param {{ addresses: object[] }} props
 */
export default function EditProfile({ addresses }) {
    const user = useShopUser();
    const defaultAddress = addresses.find((address) => address.isDefault) ?? addresses[0] ?? null;

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/profile');
    };

    return (
        <MobileLayout
            title="Ubah Profil"
            header={<AppBar title="Ubah Profil" back="/ui/profile" tone="brand" />}
        >
            <form
                onSubmit={submit}
                className="flex flex-1 flex-col items-center overflow-y-auto bg-canvas px-[22px] py-[18px]"
            >
                <div className="mx-auto mb-[18px] mt-3.5 h-[108px] w-[108px] overflow-hidden rounded-full border-[5px] border-brand">
                    <img
                        src={asset.user('01')}
                        alt={user.name}
                        className="h-full w-full object-cover"
                    />
                </div>

                <div className="w-full">
                    <Field
                        type="email"
                        name="email"
                        label="Email"
                        defaultValue={user.email}
                        autoComplete="off"
                        className="mb-2.5"
                    />
                    <Field
                        type="password"
                        name="password"
                        label="Kata Sandi Baru"
                        placeholder="••••••••"
                        autoComplete="off"
                        className="mb-2.5"
                    />
                    <Field
                        type="tel"
                        name="phone"
                        label="Nomor Telepon"
                        defaultValue={user.phone}
                        autoComplete="off"
                        className="mb-3.5"
                    />

                    <label className="mb-1 block text-[12px] font-medium text-ink">
                        Alamat Pengiriman
                    </label>

                    {defaultAddress ? (
                        <Link
                            href="/ui/my-address"
                            className="mb-2.5 flex items-start gap-2.5 border border-line bg-white p-3.5"
                        >
                            <Icon name="pin" size={19} className="mt-0.5 text-ink" />

                            <div className="flex-1">
                                <div className="mb-0.5 flex items-center gap-1.5 text-[13px] font-bold text-ink">
                                    {defaultAddress.label}
                                    {addresses.length > 1 ? (
                                        <span className="text-[11px] font-normal text-muted">
                                            +{addresses.length - 1} alamat lain
                                        </span>
                                    ) : null}
                                </div>
                                <div className="text-xs text-muted">{defaultAddress.fullAddress}</div>
                                <div className="mt-1 text-[11px] text-brand">Kelola alamat</div>
                            </div>

                            <Icon name="chevronRight" size={14} className="mt-1 text-[#cccccc]" />
                        </Link>
                    ) : (
                        <Link
                            href="/ui/add-new-address"
                            className="mb-2.5 flex items-center gap-2.5 border border-dashed border-[#cccccc] bg-white p-3.5 text-[13px] text-brand"
                        >
                            <Icon name="plus" size={16} />
                            Tambah alamat pengiriman
                        </Link>
                    )}

                    <Button type="submit">Simpan Perubahan</Button>
                </div>
            </form>
        </MobileLayout>
    );
}
