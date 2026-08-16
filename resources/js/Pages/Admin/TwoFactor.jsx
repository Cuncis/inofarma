import { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import { Field, Input } from '@/Components/Admin/Form';

/**
 * @param {{ enabled: boolean, pending: boolean, qrCodeSvg: string|null, secretKey: string|null, recoveryCodes: string[]|null }} props
 */
export default function TwoFactor({ enabled, pending, qrCodeSvg, secretKey, recoveryCodes }) {
    const { data, setData, post, processing, errors, reset } = useForm({ code: '' });
    const [confirmingDisable, setConfirmingDisable] = useState(false);

    const confirm = (event) => {
        event.preventDefault();
        post('/admin/keamanan/konfirmasi', { onSuccess: () => reset() });
    };

    return (
        <AdminLayout
            title="Keamanan"
            heading="Keamanan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Keamanan' }]}
        >
            <div className="max-w-xl space-y-5">
                <Card title="Autentikasi Dua Faktor">
                    {enabled ? (
                        <div className="space-y-4">
                            <Badge tone="success">Aktif</Badge>
                            <p className="text-[13px] text-admin-body dark:text-admin-dark-body">
                                Setiap kali masuk, Anda akan diminta kode dari aplikasi authenticator.
                            </p>
                            <div className="flex gap-2">
                                <Button
                                    variant="outline"
                                    onClick={() => router.post('/admin/keamanan/kode-pemulihan')}
                                >
                                    Buat Ulang Kode Pemulihan
                                </Button>
                                <Button variant="danger" onClick={() => setConfirmingDisable(true)}>
                                    Nonaktifkan
                                </Button>
                            </div>
                        </div>
                    ) : pending ? (
                        <div className="space-y-4">
                            <p className="text-[13px] text-admin-body dark:text-admin-dark-body">
                                Pindai kode QR ini dengan aplikasi authenticator (Google Authenticator,
                                Authy, dsb), lalu masukkan kode 6 digit untuk mengaktifkan.
                            </p>

                            {qrCodeSvg ? (
                                <div
                                    className="w-fit rounded-lg border border-admin-border bg-white p-3 dark:border-admin-dark-border"
                                    dangerouslySetInnerHTML={{ __html: qrCodeSvg }}
                                />
                            ) : null}

                            <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                Kunci manual: <code className="font-mono">{secretKey}</code>
                            </p>

                            <form onSubmit={confirm} className="flex items-end gap-2">
                                <Field label="Kode Konfirmasi" htmlFor="code" hint={errors.code} className="flex-1">
                                    <Input
                                        id="code"
                                        value={data.code}
                                        onChange={(event) => setData('code', event.target.value)}
                                        placeholder="123456"
                                        autoFocus
                                    />
                                </Field>
                                <Button type="submit" disabled={processing}>
                                    Konfirmasi
                                </Button>
                            </form>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <Badge tone="neutral">Tidak aktif</Badge>
                            <p className="text-[13px] text-admin-body dark:text-admin-dark-body">
                                Tambahkan lapisan keamanan ekstra pada akun Anda.
                            </p>
                            <Button onClick={() => router.post('/admin/keamanan/aktifkan')}>
                                Aktifkan Autentikasi Dua Faktor
                            </Button>
                        </div>
                    )}
                </Card>

                {recoveryCodes ? (
                    <Card title="Kode Pemulihan">
                        <p className="mb-3 text-[13px] text-admin-body dark:text-admin-dark-body">
                            Simpan kode ini di tempat aman — setiap kode hanya bisa dipakai satu kali,
                            dan halaman ini tidak akan menampilkannya lagi.
                        </p>
                        <div className="grid grid-cols-2 gap-2 rounded-lg bg-admin-hover p-3 font-mono text-[13px] dark:bg-admin-dark-hover">
                            {recoveryCodes.map((code) => (
                                <span key={code}>{code}</span>
                            ))}
                        </div>
                    </Card>
                ) : null}
            </div>

            <ConfirmDialog
                open={confirmingDisable}
                title="Nonaktifkan autentikasi dua faktor?"
                body="Akun Anda hanya akan dilindungi oleh kata sandi."
                confirmLabel="Nonaktifkan"
                onConfirm={() => {
                    router.delete('/admin/keamanan');
                    setConfirmingDisable(false);
                }}
                onCancel={() => setConfirmingDisable(false)}
            />
        </AdminLayout>
    );
}
