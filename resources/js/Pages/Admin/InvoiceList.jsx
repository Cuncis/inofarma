import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { money, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'number', label: 'No. Faktur' },
    { key: 'customer', label: 'Pelanggan' },
    { key: 'issued', label: 'Diterbitkan' },
    { key: 'due', label: 'Jatuh Tempo' },
    { key: 'total', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status' },
];

const statuses = ['Semua Status', 'Lunas', 'Belum Bayar', 'Jatuh Tempo', 'Refund'];

/**
 * Read-only — a faktur is an existing Order rendered as an invoice
 * (`InvoicePresenter`), so there is nothing to create here. Scoped by the
 * same branch confinement as `/admin/pesanan` (`Order`'s `BranchScope`).
 */
export default function InvoiceList({ invoices }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('Semua Status');

    const visible = invoices.filter((row) => {
        const matchesSearch =
            row.number.toLowerCase().includes(search.toLowerCase()) ||
            (row.customer ?? '').toLowerCase().includes(search.toLowerCase());

        return matchesSearch && (status === 'Semua Status' || row.status === status);
    });

    return (
        <AdminLayout
            title="Daftar Faktur"
            heading="Faktur"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Faktur' }]}
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nomor faktur atau pelanggan..."
                    filter={{ value: status, onChange: setStatus, options: statuses }}
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.number}
                    empty="Faktur tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'number') {
                            return (
                                <Link href={`/admin/faktur/${row.number}`} className="font-semibold text-brand">
                                    {row.number}
                                </Link>
                            );
                        }

                        if (key === 'total') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.total)}
                                </span>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {invoices.length} faktur
                </div>
            </Card>
        </AdminLayout>
    );
}
