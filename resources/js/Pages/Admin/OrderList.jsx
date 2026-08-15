import { useState } from 'react';
import { Link, router } from '@inertiajs/react';
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
    { key: 'id', label: 'No. Pesanan' },
    { key: 'customerName', label: 'Pelanggan' },
    { key: 'date', label: 'Tanggal' },
    { key: 'itemCount', label: 'Item', align: 'right' },
    { key: 'payment', label: 'Pembayaran' },
    { key: 'total', label: 'Total', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

export default function OrderList({ orders, statuses }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('Semua Status');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = orders.filter((order) => {
        const matchesSearch =
            order.id.toLowerCase().includes(search.toLowerCase()) ||
            order.customerName.toLowerCase().includes(search.toLowerCase());

        return matchesSearch && (status === 'Semua Status' || order.status === status);
    });

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/pesanan/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    // A completed order is a financial record, so it is cancelled, not removed.
    const isCompleted = pendingDelete?.status === 'Selesai';

    return (
        <AdminLayout
            title="Daftar Pesanan"
            heading="Pesanan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pesanan' }]}
            actions={
                <>
                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:restart-broken"
                        onClick={() =>
                            router.post('/admin/pesanan/reset', {}, { preserveScroll: true })
                        }
                    >
                        Atur Ulang Data
                    </Button>

                    <Button href="/admin/pesanan/tambah" icon="solar:add-circle-broken" size="sm">
                        Buat Pesanan
                    </Button>
                </>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nomor pesanan atau pelanggan..."
                    filter={{
                        value: status,
                        onChange: setStatus,
                        options: ['Semua Status', ...statuses],
                    }}
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Pesanan tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'id') {
                            return (
                                <Link
                                    href={`/admin/pesanan/${row.id}`}
                                    className="font-semibold text-brand hover:underline"
                                >
                                    #{row.id}
                                </Link>
                            );
                        }

                        if (key === 'customerName') {
                            return (
                                <span className="flex items-center gap-2.5">
                                    {row.customerAvatar ? (
                                        <img
                                            src={row.customerAvatar}
                                            alt=""
                                            className="h-8 w-8 rounded-full object-cover"
                                        />
                                    ) : null}
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {row.customerName}
                                    </span>
                                </span>
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
                                    label={`pesanan ${row.id}`}
                                    viewHref={`/admin/pesanan/${row.id}`}
                                    editHref={`/admin/pesanan/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {orders.length} pesanan
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title={isCompleted ? 'Pesanan sudah selesai' : 'Hapus pesanan?'}
                body={
                    pendingDelete
                        ? isCompleted
                            ? `Pesanan #${pendingDelete.id} sudah selesai dan menjadi catatan keuangan. Ubah statusnya menjadi Dibatalkan bila perlu, jangan dihapus.`
                            : `Pesanan #${pendingDelete.id} akan dihapus. Tindakan ini tidak bisa dibatalkan.`
                        : ''
                }
                confirmLabel={isCompleted ? 'Mengerti' : 'Hapus'}
                processing={deleting}
                onConfirm={isCompleted ? () => setPendingDelete(null) : confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
