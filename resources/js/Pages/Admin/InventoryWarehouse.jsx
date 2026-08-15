import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { statusTone, warehouses } from '@/Components/Admin/data';

export default function InventoryWarehouse() {
    return (
        <AdminLayout
            title="Gudang"
            heading="Gudang"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Gudang' },
            ]}
            actions={
                <Button icon="solar:add-circle-broken" size="sm">
                    Tambah Gudang
                </Button>
            }
        >
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {warehouses.map((warehouse) => (
                    <Card key={warehouse.name}>
                        <div className="mb-4 flex items-start justify-between gap-2">
                            <span className="flex h-11 w-11 items-center justify-center rounded-lg bg-blush text-brand dark:bg-brand/20 dark:text-white">
                                <Icon name="solar:box-bold-duotone" size={24} />
                            </span>

                            <Badge tone={statusTone(warehouse.status)}>{warehouse.status}</Badge>
                        </div>

                        <h2 className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {warehouse.name}
                        </h2>

                        <p className="mt-0.5 text-xs text-admin-muted dark:text-admin-dark-muted">
                            {warehouse.city} · {warehouse.manager}
                        </p>

                        <div className="mt-4">
                            <div className="mb-1.5 flex justify-between text-xs">
                                <span className="text-admin-muted dark:text-admin-dark-muted">
                                    Kapasitas terpakai
                                </span>
                                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                    {warehouse.capacity}%
                                </span>
                            </div>

                            <div
                                role="progressbar"
                                aria-valuenow={warehouse.capacity}
                                aria-valuemin={0}
                                aria-valuemax={100}
                                aria-label={`Kapasitas ${warehouse.name}`}
                                className="h-2 overflow-hidden rounded-full bg-admin-hover dark:bg-admin-dark-hover"
                            >
                                <div
                                    style={{ width: `${warehouse.capacity}%` }}
                                    className={`h-full rounded-full ${
                                        warehouse.capacity >= 90
                                            ? 'bg-danger'
                                            : warehouse.capacity >= 70
                                              ? 'bg-warning'
                                              : 'bg-success'
                                    }`}
                                />
                            </div>

                            <p className="mt-2.5 text-xs text-admin-muted dark:text-admin-dark-muted">
                                {warehouse.items.toLocaleString('id-ID')} item tersimpan
                            </p>
                        </div>
                    </Card>
                ))}
            </div>
        </AdminLayout>
    );
}
