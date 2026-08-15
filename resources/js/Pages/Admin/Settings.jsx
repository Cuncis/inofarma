import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import { DropZone, Field, Input, Select, Switch, Textarea } from '@/Components/Admin/Form';

const tabs = [
    { key: 'umum', label: 'Umum' },
    { key: 'toko', label: 'Toko' },
    { key: 'notifikasi', label: 'Notifikasi' },
    { key: 'keamanan', label: 'Keamanan' },
];

export default function Settings() {
    const [tab, setTab] = useState('umum');
    const [toggles, setToggles] = useState({
        orderEmail: true,
        stockAlert: true,
        weeklyReport: false,
        twoFactor: false,
        loginAlert: true,
    });

    const flip = (key) => setToggles((current) => ({ ...current, [key]: ! current[key] }));

    return (
        <AdminLayout
            title="Pengaturan"
            heading="Pengaturan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pengaturan' }]}
        >
            <div className="mx-auto max-w-4xl">
                <div className="mb-5 flex flex-wrap gap-2">
                    {tabs.map((item) => (
                        <button
                            key={item.key}
                            type="button"
                            onClick={() => setTab(item.key)}
                            className={`rounded-lg px-4 py-2 text-[13px] font-semibold transition-colors ${
                                tab === item.key
                                    ? 'bg-brand text-white'
                                    : 'border border-admin-border text-admin-body hover:bg-admin-hover dark:border-admin-dark-border dark:text-admin-dark-body dark:hover:bg-admin-dark-hover'
                            }`}
                        >
                            {item.label}
                        </button>
                    ))}
                </div>

                <form onSubmit={(event) => event.preventDefault()}>
                    {tab === 'umum' ? (
                        <Card title="Profil Akun">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field label="Nama Lengkap" htmlFor="name">
                                    <Input id="name" defaultValue="Kirana Wijaya" />
                                </Field>
                                <Field label="Email" htmlFor="email">
                                    <Input id="email" type="email" defaultValue="kirana.wijaya@inofarma.co.id" />
                                </Field>
                                <Field label="Nomor Telepon" htmlFor="phone">
                                    <Input id="phone" type="tel" defaultValue="+62 812-3456-7890" />
                                </Field>
                                <Field label="Bahasa" htmlFor="locale">
                                    <Select id="locale" options={['Bahasa Indonesia', 'English']} />
                                </Field>
                                <Field label="Zona Waktu" htmlFor="timezone">
                                    <Select id="timezone" options={['WIB (UTC+7)', 'WITA (UTC+8)', 'WIT (UTC+9)']} />
                                </Field>
                                <Field label="Mata Uang" htmlFor="currency">
                                    <Select id="currency" options={['IDR — Rupiah']} />
                                </Field>
                                <Field label="Foto Profil" htmlFor="avatar" className="sm:col-span-2">
                                    <DropZone hint="JPG atau PNG maksimal 2 MB" />
                                </Field>
                            </div>
                        </Card>
                    ) : null}

                    {tab === 'toko' ? (
                        <Card title="Informasi Apotek">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field label="Nama Apotek" htmlFor="store">
                                    <Input id="store" defaultValue="Inofarma" />
                                </Field>
                                <Field label="Nomor Izin (SIA)" htmlFor="license">
                                    <Input id="license" defaultValue="SIA/2025/00123" />
                                </Field>
                                <Field label="Telepon Apotek" htmlFor="storePhone">
                                    <Input id="storePhone" type="tel" defaultValue="+62 21 5555 1234" />
                                </Field>
                                <Field label="Email Apotek" htmlFor="storeEmail">
                                    <Input id="storeEmail" type="email" defaultValue="halo@inofarma.co.id" />
                                </Field>
                                <Field label="Alamat" htmlFor="storeAddress" className="sm:col-span-2">
                                    <Textarea
                                        id="storeAddress"
                                        defaultValue="Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190"
                                    />
                                </Field>
                            </div>
                        </Card>
                    ) : null}

                    {tab === 'notifikasi' ? (
                        <Card title="Preferensi Notifikasi">
                            <div className="space-y-5">
                                {[
                                    { key: 'orderEmail', label: 'Email saat ada pesanan baru', hint: 'Kirim pemberitahuan setiap pesanan masuk.' },
                                    { key: 'stockAlert', label: 'Peringatan stok menipis', hint: 'Beri tahu saat stok produk di bawah ambang batas.' },
                                    { key: 'weeklyReport', label: 'Laporan mingguan', hint: 'Ringkasan penjualan setiap Senin pagi.' },
                                ].map((item) => (
                                    <div
                                        key={item.key}
                                        className="flex items-start justify-between gap-4 border-b border-admin-border pb-4 last:border-0 last:pb-0 dark:border-admin-dark-border"
                                    >
                                        <div>
                                            <p className="text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                                {item.label}
                                            </p>
                                            <p className="mt-0.5 text-xs text-admin-muted dark:text-admin-dark-muted">
                                                {item.hint}
                                            </p>
                                        </div>

                                        <Switch checked={toggles[item.key]} onChange={() => flip(item.key)} />
                                    </div>
                                ))}
                            </div>
                        </Card>
                    ) : null}

                    {tab === 'keamanan' ? (
                        <Card title="Keamanan Akun">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <Field label="Kata Sandi Saat Ini" htmlFor="current">
                                    <Input id="current" type="password" placeholder="••••••••" />
                                </Field>
                                <div className="hidden sm:block" />
                                <Field label="Kata Sandi Baru" htmlFor="new">
                                    <Input id="new" type="password" placeholder="••••••••" />
                                </Field>
                                <Field label="Ulangi Kata Sandi Baru" htmlFor="confirm">
                                    <Input id="confirm" type="password" placeholder="••••••••" />
                                </Field>
                            </div>

                            <div className="mt-5 space-y-5 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                                {[
                                    { key: 'twoFactor', label: 'Verifikasi dua langkah', hint: 'Minta kode tambahan setiap kali masuk.' },
                                    { key: 'loginAlert', label: 'Peringatan login baru', hint: 'Beri tahu saat ada login dari perangkat asing.' },
                                ].map((item) => (
                                    <div key={item.key} className="flex items-start justify-between gap-4">
                                        <div>
                                            <p className="text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                                {item.label}
                                            </p>
                                            <p className="mt-0.5 text-xs text-admin-muted dark:text-admin-dark-muted">
                                                {item.hint}
                                            </p>
                                        </div>

                                        <Switch checked={toggles[item.key]} onChange={() => flip(item.key)} />
                                    </div>
                                ))}
                            </div>
                        </Card>
                    ) : null}

                    <div className="mt-5 flex gap-2">
                        <Button type="submit">Simpan Perubahan</Button>
                        <Button href="/admin" variant="outline">
                            Batal
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
