import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Card from '@/Components/Admin/Card';
import StatCard from '@/Components/Admin/StatCard';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

/**
 * "Rekonsiliasi harian, dipecah per cabang untuk setoran" (ROADMAP.md Fase
 * 6). The top table is what actually matters for reconciling against the
 * bank; the log below it is for chasing a stuck or failed payment.
 *
 * @param {{
 *   daily: { branch: string, tanggal: string, jumlahPesanan: number, total: number }[],
 *   log: object[],
 *   grandTotal: number,
 *   from: string,
 *   to: string,
 * }} props
 */
export default function PaymentReconciliation({ daily, log, grandTotal, from, to }) {
    const applyRange = (event) => {
        event.preventDefault();

        const form = new FormData(event.target);

        router.get('/admin/rekonsiliasi', {
            dari: form.get('dari'),
            sampai: form.get('sampai'),
        });
    };

    return (
        <AdminLayout
            title="Rekonsiliasi Pembayaran"
            heading="Rekonsiliasi Pembayaran"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Rekonsiliasi' },
            ]}
        >
            <div className="mb-5 grid gap-4 sm:grid-cols-2">
                <StatCard
                    label="Terkumpul dalam Rentang Ini"
                    value={money(grandTotal)}
                    icon="solar:wallet-money-bold-duotone"
                />

                <Card>
                    <form onSubmit={applyRange} className="flex flex-wrap items-end gap-3">
                        <label className="text-[13px]">
                            <span className="mb-1 block text-admin-muted dark:text-admin-dark-muted">Dari</span>
                            <input
                                type="date"
                                name="dari"
                                defaultValue={from}
                                className="rounded-md border border-admin-border bg-transparent px-3 py-1.5 text-[13px] dark:border-admin-dark-border"
                            />
                        </label>

                        <label className="text-[13px]">
                            <span className="mb-1 block text-admin-muted dark:text-admin-dark-muted">Sampai</span>
                            <input
                                type="date"
                                name="sampai"
                                defaultValue={to}
                                className="rounded-md border border-admin-border bg-transparent px-3 py-1.5 text-[13px] dark:border-admin-dark-border"
                            />
                        </label>

                        <button
                            type="submit"
                            className="rounded-md bg-brand px-4 py-1.5 text-[13px] font-semibold text-white"
                        >
                            Terapkan
                        </button>
                    </form>
                </Card>
            </div>

            <Card title="Terkumpul per Cabang per Hari" bodyClassName="p-0" className="mb-5">
                <Table
                    columns={[
                        { key: 'tanggal', label: 'Tanggal' },
                        { key: 'branch', label: 'Cabang' },
                        { key: 'jumlahPesanan', label: 'Jumlah Pesanan', align: 'right' },
                        { key: 'total', label: 'Total Terkumpul', align: 'right' },
                    ]}
                    rows={daily}
                    rowKey={(row) => `${row.tanggal}-${row.branch}`}
                    renderCell={(row, key) => {
                        if (key === 'total') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.total)}
                                </span>
                            );
                        }

                        return row[key];
                    }}
                    empty="Belum ada pembayaran lunas pada rentang tanggal ini."
                />
            </Card>

            <Card title="Log Pembayaran Terbaru" bodyClassName="p-0">
                <Table
                    columns={[
                        { key: 'orderNumber', label: 'No. Pesanan' },
                        { key: 'branch', label: 'Cabang' },
                        { key: 'channel', label: 'Kanal' },
                        { key: 'amount', label: 'Jumlah', align: 'right' },
                        { key: 'status', label: 'Status' },
                        { key: 'createdAt', label: 'Waktu' },
                        { key: 'actions', label: '', align: 'right' },
                    ]}
                    rows={log}
                    rowKey={(row) => row.invoiceNumber}
                    renderCell={(row, key) => {
                        if (key === 'amount') {
                            return money(row.amount);
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            // Only a still-open attempt has anything to learn from
                            // DOKU — success/expired/refunded are already final.
                            if (row.status !== 'Pending') {
                                return null;
                            }

                            return (
                                <button
                                    type="button"
                                    onClick={() => router.post(
                                        `/admin/rekonsiliasi/${row.invoiceNumber}/cek-status`,
                                        {},
                                        { preserveScroll: true },
                                    )}
                                    className="text-xs font-medium text-brand hover:underline"
                                >
                                    Cek Status
                                </button>
                            );
                        }

                        return row[key] ?? '—';
                    }}
                />
            </Card>
        </AdminLayout>
    );
}
