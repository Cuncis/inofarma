import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import RowActions from '@/Components/Admin/RowActions';
import Table from '@/Components/Admin/Table';
import TableToolbar from '@/Components/Admin/TableToolbar';
import { money, statusTone } from '@/Components/Admin/data';

const columns = [
    { key: 'code', label: 'Kode' },
    { key: 'type', label: 'Tipe' },
    { key: 'value', label: 'Nilai' },
    { key: 'minimumPurchase', label: 'Min. Belanja', align: 'right' },
    { key: 'usedCount', label: 'Terpakai', align: 'right' },
    { key: 'branches', label: 'Cabang' },
    { key: 'expiresAt', label: 'Berlaku Sampai' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function CouponList({ coupons }) {
    const [search, setSearch] = useState('');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = coupons.filter((row) => row.code.toLowerCase().includes(search.toLowerCase()));

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/kupon/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    return (
        <AdminLayout
            title="Daftar Kupon"
            heading="Kupon"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Kupon' }]}
            actions={
                <Button href="/admin/kupon/tambah" icon="solar:add-circle-broken" size="sm">
                    Tambah Kupon
                </Button>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder="Cari kode kupon..." />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.code}
                    empty="Kupon tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'code') {
                            return (
                                <code className="rounded bg-blush px-2 py-1 text-xs font-bold tracking-wider text-brand dark:bg-brand/20 dark:text-white">
                                    {row.code}
                                </code>
                            );
                        }

                        if (key === 'value') {
                            if (row.type === 'Gratis Ongkir') {
                                return 'Gratis';
                            }

                            return row.type === 'Persentase' ? `${row.value}%` : money(row.value);
                        }

                        if (key === 'minimumPurchase') {
                            return row.minimumPurchase ? money(row.minimumPurchase) : '—';
                        }

                        if (key === 'usedCount') {
                            return row.quota ? `${row.usedCount} / ${row.quota}` : row.usedCount;
                        }

                        if (key === 'branches') {
                            return row.appliesToAllBranches ? (
                                <span className="text-admin-muted dark:text-admin-dark-muted">Semua cabang</span>
                            ) : (
                                `${row.branchNames.length} cabang`
                            );
                        }

                        if (key === 'expiresAt') {
                            return row.expiresAt ?? '—';
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.code}
                                    editHref={`/admin/kupon/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title="Hapus kupon?"
                body={pendingDelete ? `"${pendingDelete.code}" akan dihapus. Tindakan ini tidak bisa dibatalkan.` : ''}
                confirmLabel="Hapus"
                processing={deleting}
                onConfirm={confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
