import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'name', label: 'Cabang' },
    { key: 'kota', label: 'Kota' },
    { key: 'products', label: 'Produk Distok', align: 'right' },
    { key: 'lowStock', label: 'Stok Menipis', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

/**
 * Pick a branch to see its own stock. Reads for the matrix (every branch at
 * once) live on a separate screen — {@link StockMatrix} — this one is about
 * going deep on one branch.
 */
export default function BranchStockList({ branches }) {
    const [search, setSearch] = useState('');

    const visible = branches.filter(
        (branch) =>
            branch.name.toLowerCase().includes(search.toLowerCase()) ||
            branch.kota.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Stok per Cabang"
            heading="Stok per Cabang"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Stok per Cabang' },
            ]}
            actions={
                <Button
                    href="/admin/inventaris/matriks"
                    variant="outline"
                    size="sm"
                    icon="solar:widget-2-bold-duotone"
                >
                    Lihat Matriks
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama cabang atau kota..."
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Cabang tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'products') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {row.products}
                                </span>
                            );
                        }

                        if (key === 'lowStock') {
                            return row.lowStock > 0 ? (
                                <Badge tone="warning">{row.lowStock}</Badge>
                            ) : (
                                <span className="text-admin-muted dark:text-admin-dark-muted">0</span>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <Button
                                    href={`/admin/inventaris/stok/${row.id}`}
                                    variant="outline"
                                    size="sm"
                                >
                                    Lihat Stok
                                </Button>
                            );
                        }

                        return row[key];
                    }}
                />
            </Card>
        </AdminLayout>
    );
}
