import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { DropZone } from '@/Components/Admin/Form';

/**
 * Bulk product import from a Shopify-format product-export CSV.
 *
 * @param {{ result?: {
 *   created: number, updated: number, warnedInactive: number,
 *   imagesFailed: string[], failed: { row: number, message: string }[],
 * } }} props
 */
export default function ProductImport({ result }) {
    const [file, setFile] = useState(null);
    const [importing, setImporting] = useState(false);

    const submit = () => {
        if (! file) {
            return;
        }

        setImporting(true);

        router.post(
            '/admin/produk/impor',
            { file },
            { forceFormData: true, preserveScroll: true, onFinish: () => setImporting(false) },
        );
    };

    return (
        <AdminLayout
            title="Impor Produk"
            heading="Impor Produk"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Produk', href: '/admin/produk' },
                { label: 'Impor' },
            ]}
        >
            <Card title="Berkas CSV">
                <div className="space-y-4">
                    <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                        Mengunggah ekspor produk CSV (format Shopify). Produk dicocokkan lewat
                        SKU (kolom "Variant SKU") — mengimpor berkas yang sama dua kali akan
                        memperbarui baris yang sudah ada, bukan menggandakannya.
                    </p>

                    {file ? (
                        <div className="flex items-center justify-between rounded-lg border border-admin-border px-4 py-3 dark:border-admin-dark-border">
                            <div className="flex items-center gap-2 text-[13px] text-admin-heading dark:text-admin-dark-heading">
                                <Icon name="solar:file-broken" size={18} className="text-admin-muted dark:text-admin-dark-muted" />
                                {file.name}
                            </div>

                            <button
                                type="button"
                                onClick={() => setFile(null)}
                                disabled={importing}
                                className="text-admin-muted hover:text-danger disabled:opacity-30 dark:text-admin-dark-muted"
                            >
                                <Icon name="solar:close-circle-broken" size={18} />
                            </button>
                        </div>
                    ) : (
                        <DropZone
                            hint="CSV maksimal 10 MB"
                            accept=".csv,text/csv"
                            onFiles={(files) => setFile(files[0])}
                        />
                    )}

                    <Button onClick={submit} disabled={! file || importing}>
                        {importing ? 'Mengimpor…' : 'Impor Produk'}
                    </Button>
                </div>
            </Card>

            {result ? (
                <Card title="Hasil Impor" className="mt-5">
                    <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <Stat label="Produk baru" value={result.created} />
                            <Stat label="Produk diperbarui" value={result.updated} />
                            <Stat label="Dinonaktifkan (perlu peringatan)" value={result.warnedInactive} tone="warning" />
                            <Stat label="Baris gagal" value={result.failed.length} tone={result.failed.length ? 'danger' : undefined} />
                        </div>

                        {result.warnedInactive > 0 ? (
                            <p className="rounded-lg bg-admin-hover px-4 py-3 text-xs text-admin-muted dark:bg-admin-dark-hover dark:text-admin-dark-muted">
                                {result.warnedInactive} produk tergolong obat bebas terbatas. CSV tidak
                                membawa teks peringatan P1–P6, jadi produk ini diimpor dengan status
                                Nonaktif — tambahkan peringatannya lalu aktifkan secara manual sebelum
                                tampil di toko.
                            </p>
                        ) : null}

                        {result.imagesFailed.length > 0 ? (
                            <div>
                                <p className="mb-1.5 text-xs font-medium text-admin-heading dark:text-admin-dark-heading">
                                    Gambar gagal diunduh untuk {result.imagesFailed.length} SKU:
                                </p>
                                <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                    {result.imagesFailed.join(', ')}
                                </p>
                            </div>
                        ) : null}

                        {result.failed.length > 0 ? (
                            <div>
                                <p className="mb-1.5 text-xs font-medium text-admin-heading dark:text-admin-dark-heading">
                                    Baris yang gagal diimpor:
                                </p>
                                <ul className="space-y-1 text-xs text-admin-muted dark:text-admin-dark-muted">
                                    {result.failed.map((item) => (
                                        <li key={item.row}>
                                            Baris {item.row}: {item.message}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ) : null}
                    </div>
                </Card>
            ) : null}
        </AdminLayout>
    );
}

function Stat({ label, value, tone }) {
    const toneClass = {
        warning: 'text-warning-deep',
        danger: 'text-danger',
    }[tone];

    return (
        <div className="rounded-lg border border-admin-border px-4 py-3 dark:border-admin-dark-border">
            <div className={`text-xl font-semibold ${toneClass ?? 'text-admin-heading dark:text-admin-dark-heading'}`}>
                {value}
            </div>
            <div className="mt-0.5 text-xs text-admin-muted dark:text-admin-dark-muted">{label}</div>
        </div>
    );
}
