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
    { key: 'name', label: 'Pelanggan' },
    { key: 'phone', label: 'Telepon' },
    { key: 'city', label: 'Kota' },
    { key: 'orders', label: 'Pesanan', align: 'right' },
    { key: 'spent', label: 'Total Belanja', align: 'right' },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: '', align: 'right' },
];

const statusOptions = ['Semua Status', 'Aktif', 'Nonaktif'];

export default function CustomerList({ customers }) {
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('Semua Status');
    const [pendingDelete, setPendingDelete] = useState(null);
    const [deleting, setDeleting] = useState(false);

    const visible = customers.filter((customer) => {
        const matchesSearch =
            customer.name.toLowerCase().includes(search.toLowerCase()) ||
            customer.email.toLowerCase().includes(search.toLowerCase());

        return matchesSearch && (status === 'Semua Status' || customer.status === status);
    });

    const confirmDelete = () => {
        setDeleting(true);

        router.delete(`/admin/pelanggan/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setDeleting(false);
                setPendingDelete(null);
            },
        });
    };

    // Order history keeps a customer on file, so say so before the request.
    const hasOrders = pendingDelete?.orders > 0;

    return (
        <AdminLayout
            title="Daftar Pelanggan"
            heading="Pelanggan"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Pelanggan' }]}
            actions={
                <>
                    <Button
                        variant="outline"
                        size="sm"
                        icon="solar:restart-broken"
                        onClick={() =>
                            router.post('/admin/pelanggan/reset', {}, { preserveScroll: true })
                        }
                    >
                        Atur Ulang Data
                    </Button>

                    <Button href="/admin/pelanggan/tambah" icon="solar:add-circle-broken" size="sm">
                        Tambah Pelanggan
                    </Button>
                </>
            }
        >
            <Card bodyClassName="p-0">
                <TableToolbar
                    search={search}
                    onSearch={setSearch}
                    placeholder="Cari nama atau email..."
                    filter={{ value: status, onChange: setStatus, options: statusOptions }}
                />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={(row) => row.id}
                    empty="Pelanggan tidak ditemukan."
                    renderCell={(row, key) => {
                        if (key === 'name') {
                            return (
                                <Link
                                    href={`/admin/pelanggan/${row.id}`}
                                    className="flex items-center gap-2.5"
                                >
                                    <img
                                        src={row.avatar}
                                        alt=""
                                        className="h-9 w-9 rounded-full object-cover"
                                    />
                                    <span>
                                        <span className="block font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {row.name}
                                        </span>
                                        <span className="block text-xs text-admin-muted dark:text-admin-dark-muted">
                                            {row.email}
                                        </span>
                                    </span>
                                </Link>
                            );
                        }

                        if (key === 'spent') {
                            return (
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {money(row.spent)}
                                </span>
                            );
                        }

                        if (key === 'status') {
                            return <Badge tone={statusTone(row.status)}>{row.status}</Badge>;
                        }

                        if (key === 'actions') {
                            return (
                                <RowActions
                                    label={row.name}
                                    viewHref={`/admin/pelanggan/${row.id}`}
                                    editHref={`/admin/pelanggan/${row.id}/ubah`}
                                    onDelete={() => setPendingDelete(row)}
                                />
                            );
                        }

                        return row[key];
                    }}
                />

                <div className="border-t border-admin-border px-5 py-3 text-xs text-admin-muted dark:border-admin-dark-border dark:text-admin-dark-muted">
                    Menampilkan {visible.length} dari {customers.length} pelanggan
                </div>
            </Card>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title={hasOrders ? 'Pelanggan punya riwayat pesanan' : 'Hapus pelanggan?'}
                body={
                    pendingDelete
                        ? hasOrders
                            ? `"${pendingDelete.name}" memiliki ${pendingDelete.orders} pesanan. Menghapusnya akan memutus riwayat tersebut — ubah statusnya menjadi Nonaktif jika ingin menonaktifkan akun.`
                            : `"${pendingDelete.name}" akan dihapus. Tindakan ini tidak bisa dibatalkan.`
                        : ''
                }
                confirmLabel={hasOrders ? 'Mengerti' : 'Hapus'}
                processing={deleting}
                onConfirm={hasOrders ? () => setPendingDelete(null) : confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </AdminLayout>
    );
}
