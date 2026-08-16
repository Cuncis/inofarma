import { useMemo, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';

/**
 * One product × every branch, read-only. Corrections happen on a branch's own
 * stock page, where an adjustment can also carry a reason.
 */
export default function StockMatrix({ branches, rows }) {
    const [search, setSearch] = useState('');

    const columns = useMemo(
        () => [
            { key: 'productName', label: 'Produk' },
            ...branches.map((branch) => ({
                key: `branch:${branch.id}`,
                label: branch.name.replace('Apotek Inofarma ', ''),
                align: 'right',
            })),
        ],
        [branches],
    );

    const tableRows = useMemo(
        () =>
            rows.map((row) => ({
                productId: row.productId,
                productName: row.productName,
                category: row.category,
                ...Object.fromEntries(
                    branches.map((branch, index) => [`branch:${branch.id}`, row.cells[index]]),
                ),
            })),
        [rows, branches],
    );

    const visible = tableRows.filter((row) =>
        row.productName.toLowerCase().includes(search.toLowerCase()),
    );

    return (
        <AdminLayout
            title="Matriks Stok"
            heading="Matriks Stok"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Matriks Stok' },
            ]}
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari produk..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.productId}
                    empty="Produk tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'productName') {
                            return (
                                <div className="flex flex-col">
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.productName}
                                    </span>
                                    <span className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {row.category}
                                    </span>
                                </div>
                            );
                        }

                        const cell = row[key];

                        if (! cell) {
                            return <span className="text-admin-muted dark:text-admin-dark-muted">—</span>;
                        }

                        return (
                            <span
                                className={
                                    cell.quantity === 0
                                        ? 'font-semibold text-danger'
                                        : cell.isLow
                                          ? 'font-semibold text-warning-deep'
                                          : 'text-admin-body dark:text-admin-dark-body'
                                }
                            >
                                {cell.quantity}
                            </span>
                        );
                    }}
                />
            </Card>
        </AdminLayout>
    );
}
