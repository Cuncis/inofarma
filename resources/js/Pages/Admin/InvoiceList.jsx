import { useState } from 'react';
import { Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { invoices as seed, money, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'number', label: 'No. Faktur' },
    { key: 'customer', label: 'Pelanggan' },
    { key: 'issued', label: 'Diterbitkan' },
    { key: 'due', label: 'Jatuh Tempo' },
    { key: 'total', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

const statuses = ['Semua Status', 'Lunas', 'Belum Bayar', 'Jatuh Tempo'];

export default function InvoiceList() {
    const [rows, setRows] = useState(seed);
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('Semua Status');

    const visible = rows.filter((row) => {
        const matchesSearch =
            row.number.toLowerCase().includes(search.toLowerCase()) ||
            row.customer.toLowerCase().includes(search.toLowerCase());

        return matchesSearch && (status === 'Semua Status' || row.status === status);
    });

    return (
        <AdminLayout
            title="Daftar Faktur"
            heading="Faktur"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Faktur' }]}
            actions={
                <Button href="/admin/faktur/tambah" icon="solar:add-circle-broken" size="sm">
                    Buat Faktur
                </Button>
            }
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
                                <Link
                                    href="/admin/faktur/detail"
                                    className="font-semibold text-brand hover:underline"
                                >
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

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.number}
                                    viewHref="/admin/faktur/detail"
                                    editHref="/admin/faktur/ubah"
                                    onDelete={() =>
                                        setRows((current) =>
                                            current.filter((item) => item.number !== row.number),
                                        )
                                    }
                                />
                            );
                        }

                        return row[key];
                    }}
                />
            </Card>
        </AdminLayout>
    );
}
