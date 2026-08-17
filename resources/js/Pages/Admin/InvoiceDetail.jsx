import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import { money, statusTone } from '@/Components/Admin/data';

/**
 * Read-only, generated from the order's own snapshot totals — nothing here
 * is recomputed from live prices (see `Order`'s docblock).
 */
export default function InvoiceDetail({ invoice }) {
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
                <Button
                    variant="outline"
                    size="sm"
                    icon="solar:printer-broken"
                    onClick={() => window.print()}
                >
                    Cetak
                </Button>
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
        </AdminLayout>
    );
}
