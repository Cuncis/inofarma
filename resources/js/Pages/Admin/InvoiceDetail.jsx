import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import { Field, Textarea } from '@/Components/Admin/Form';
import Modal from '@/Components/Admin/Modal';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

/**
 * Read-only, generated from the order's own snapshot totals — nothing here
 * is recomputed from live prices (see `Order`'s docblock) — except the
 * "Catat Refund" action (Fase 6), which only *records* a refund; see
 * `Admin\InvoiceController::refund()` for why it doesn't call DOKU.
 */
export default function InvoiceDetail({ invoice }) {
    const [refunding, setRefunding] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({ note: '' });

    const submitRefund = (event) => {
        event.preventDefault();

        post(`/admin/faktur/${invoice.number}/refund`, {
            preserveScroll: true,
            onSuccess: () => {
                setRefunding(false);
                reset();
            },
        });
    };

    return (
        <AdminLayout
            title="Detail Faktur"
            heading={invoice.number}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Faktur', href: '/admin/faktur' },
                { label: invoice.number },
            ]}
            actions={
                <div className="flex gap-2">
                    {invoice.isRefundable ? (
                        <Button
                            variant="outline"
                            size="sm"
                            icon="solar:card-recive-broken"
                            onClick={() => setRefunding(true)}
                        >
                            Catat Refund
                        </Button>
                    ) : null}

                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:printer-broken"
                        onClick={() => window.print()}
                    >
                        Cetak
                    </Button>
                </div>
            }
        >
            <Card className="mx-auto max-w-3xl">
                <div className="flex flex-wrap items-start justify-between gap-4 border-b border-admin-border pb-5 dark:border-admin-dark-border">
                    <div>
                        <p className="text-lg font-bold text-admin-heading dark:text-admin-dark-heading">
                            Inofarma
                        </p>
                        <p className="mt-1 text-xs leading-relaxed text-admin-muted dark:text-admin-dark-muted">
                            {invoice.branch}
                        </p>
                    </div>

                    <div className="text-right">
                        <p className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {invoice.number}
                        </p>
                        <Badge tone={statusTone(invoice.status)} className="mt-1.5">
                            {invoice.status}
                        </Badge>
                    </div>
                </div>

                <div className="grid gap-5 border-b border-admin-border py-5 dark:border-admin-dark-border sm:grid-cols-3">
                    <div>
                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">Ditagihkan kepada</p>
                        <p className="mt-1 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {invoice.customer}
                        </p>
                    </div>
                    <div>
                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">Tanggal terbit</p>
                        <p className="mt-1 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {invoice.issued}
                        </p>
                    </div>
                    <div>
                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">Jatuh tempo</p>
                        <p className="mt-1 text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {invoice.due}
                        </p>
                    </div>
                </div>

                <Table
                    columns={[
                        { key: 'name', label: 'Item' },
                        { key: 'qty', label: 'Jumlah', align: 'right' },
                        { key: 'price', label: 'Harga', align: 'right' },
                        { key: 'sum', label: 'Subtotal', align: 'right' },
                    ]}
                    rows={invoice.items}
                    rowKey={(row) => row.name}
                    renderCell={(row, key) => {
                        if (key === 'price') {
                            return money(row.price);
                        }

                        if (key === 'sum') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.qty * row.price)}
                                </span>
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="ml-auto mt-5 w-full max-w-xs space-y-2 text-[13px]">
                    <div className="flex justify-between">
                        <span className="text-admin-muted dark:text-admin-dark-muted">Subtotal</span>
                        <span>{money(invoice.subtotal)}</span>
                    </div>
                    {invoice.discount > 0 ? (
                        <div className="flex justify-between">
                            <span className="text-admin-muted dark:text-admin-dark-muted">Diskon</span>
                            <span>-{money(invoice.discount)}</span>
                        </div>
                    ) : null}
                    <div className="flex justify-between">
                        <span className="text-admin-muted dark:text-admin-dark-muted">Ongkos Kirim</span>
                        <span>{money(invoice.shipping)}</span>
                    </div>
                    <div className="flex justify-between">
                        <span className="text-admin-muted dark:text-admin-dark-muted">PPN</span>
                        <span>{money(invoice.tax)}</span>
                    </div>
                    <div className="flex justify-between border-t border-admin-border pt-2 text-[15px] font-bold text-admin-heading dark:border-admin-dark-border dark:text-admin-dark-heading">
                        <span>Total</span>
                        <span>{money(invoice.total)}</span>
                    </div>
                </div>
            </Card>

            {invoice.payments.length > 0 ? (
                <Card title="Riwayat Pembayaran" className="mx-auto mt-5 max-w-3xl" bodyClassName="p-0">
                    <Table
                        columns={[
                            { key: 'invoiceNumber', label: 'No. Invoice DOKU' },
                            { key: 'channel', label: 'Kanal' },
                            { key: 'amount', label: 'Jumlah', align: 'right' },
                            { key: 'status', label: 'Status' },
                            { key: 'createdAt', label: 'Dibuat' },
                            { key: 'paidAt', label: 'Dibayar' },
                        ]}
                        rows={invoice.payments}
                        rowKey={(row) => row.invoiceNumber}
                        renderCell={(row, key) => {
                            if (key === 'amount') {
                                return money(row.amount);
                            }

                            if (key === 'status') {
                                return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                            }

                            return row[key] ?? '—';
                        }}
                    />
                </Card>
            ) : null}

            <Modal open={refunding} title="Catat Refund" onClose={() => setRefunding(false)}>
                <form onSubmit={submitRefund} className="space-y-4">
                    <p className="text-[13px] text-admin-muted dark:text-admin-dark-muted">
                        DOKU tidak menyediakan refund otomatis untuk semua kanal — catat di
                        sini setelah dana benar-benar dikembalikan ke pelanggan (transfer
                        manual, dsb). Status faktur akan berubah menjadi Refund.
                    </p>

                    <Field label="Catatan" htmlFor="note" hint={errors.note}>
                        <Textarea
                            id="note"
                            value={data.note}
                            onChange={(event) => setData('note', event.target.value)}
                            placeholder="Mis. Ditransfer manual ke rekening pelanggan, 20 Agu 2026"
                        />
                    </Field>

                    <div className="flex justify-end gap-2 border-t border-admin-border pt-4 dark:border-admin-dark-border">
                        <Button type="button" variant="outline" size="sm" onClick={() => setRefunding(false)}>
                            Batal
                        </Button>
                        <Button type="submit" size="sm" disabled={processing}>
                            {processing ? 'Menyimpan…' : 'Catat Refund'}
                        </Button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
