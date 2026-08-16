import { useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog';
import Icon from '@/Components/Admin/Icon';
import { statusTone } from '@/Components/Admin/data';

const STEPS = [
    { status: 'Diminta', icon: 'solar:clipboard-list-bold-duotone' },
    { status: 'Dikirim', icon: 'solar:box-bold-duotone' },
    { status: 'Diterima', icon: 'solar:check-circle-bold-duotone' },
];

export default function StockTransferDetail({ transfer }) {
    const [cancelling, setCancelling] = useState(false);
    const [processing, setProcessing] = useState(false);

    const currentStep = STEPS.findIndex((step) => step.status === transfer.status);
    const isCancelled = transfer.status === 'Dibatalkan';

    const act = (action) => {
        setProcessing(true);

        router.post(`/admin/inventaris/transfer/${transfer.id}/${action}`, {}, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <AdminLayout
            title={`Transfer ${transfer.id}`}
            heading={`Transfer ${transfer.id}`}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Inventaris' },
                { label: 'Transfer Stok', href: '/admin/inventaris/transfer' },
                { label: transfer.id },
            ]}
            actions={
                <>
                    {transfer.canBeCancelled ? (
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => setCancelling(true)}
                            disabled={processing}
                        >
                            Batalkan
                        </Button>
                    ) : null}
                    {transfer.canBeShipped ? (
                        <Button size="sm" onClick={() => act('kirim')} disabled={processing}>
                            Tandai Dikirim
                        </Button>
                    ) : null}
                    {transfer.canBeReceived ? (
                        <Button size="sm" onClick={() => act('terima')} disabled={processing}>
                            Tandai Diterima
                        </Button>
                    ) : null}
                </>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card title="Detail Transfer" className="lg:col-span-2">
                    <div className="mb-5">
                        <Badge tone={statusTone(transfer.status)}>{transfer.status}</Badge>
                    </div>

                    {! isCancelled ? (
                        <ol className="mb-6 flex items-center gap-2">
                            {STEPS.map((step, index) => (
                                <li key={step.status} className="flex flex-1 items-center gap-2">
                                    <span
                                        className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${
                                            index <= currentStep
                                                ? 'bg-brand text-white'
                                                : 'bg-admin-hover text-admin-muted dark:bg-admin-dark-hover dark:text-admin-dark-muted'
                                        }`}
                                    >
                                        <Icon name={step.icon} size={18} />
                                    </span>
                                    <span
                                        className={`text-xs font-medium ${
                                            index <= currentStep
                                                ? 'text-admin-heading dark:text-admin-dark-heading'
                                                : 'text-admin-muted dark:text-admin-dark-muted'
                                        }`}
                                    >
                                        {step.status}
                                    </span>
                                    {index < STEPS.length - 1 ? (
                                        <span
                                            className={`h-px flex-1 ${
                                                index < currentStep
                                                    ? 'bg-brand'
                                                    : 'bg-admin-border dark:bg-admin-dark-border'
                                            }`}
                                        />
                                    ) : null}
                                </li>
                            ))}
                        </ol>
                    ) : null}

                    <dl className="space-y-3 text-[13px]">
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Produk</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {transfer.productName}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Jumlah</dt>
                            <dd className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {transfer.quantity}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Dari</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {transfer.fromBranchName}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Ke</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {transfer.toBranchName}
                            </dd>
                        </div>
                        {transfer.note ? (
                            <div className="border-t border-admin-border pt-3 dark:border-admin-dark-border">
                                <dt className="mb-1 text-admin-muted dark:text-admin-dark-muted">Catatan</dt>
                                <dd className="text-admin-body dark:text-admin-dark-body">{transfer.note}</dd>
                            </div>
                        ) : null}
                    </dl>
                </Card>

                <Card title="Riwayat">
                    <ul className="space-y-4 text-[13px]">
                        <li className="flex justify-between">
                            <span className="text-admin-muted dark:text-admin-dark-muted">Diminta</span>
                            <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {transfer.requestedAt}
                            </span>
                        </li>
                        {transfer.shippedAt ? (
                            <li className="flex justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">Dikirim</span>
                                <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {transfer.shippedAt}
                                </span>
                            </li>
                        ) : null}
                        {transfer.receivedAt ? (
                            <li className="flex justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">Diterima</span>
                                <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {transfer.receivedAt}
                                </span>
                            </li>
                        ) : null}
                        {transfer.cancelledAt ? (
                            <li className="flex justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">Dibatalkan</span>
                                <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                    {transfer.cancelledAt}
                                </span>
                            </li>
                        ) : null}
                    </ul>
                </Card>
            </div>

            <ConfirmDialog
                open={cancelling}
                title="Batalkan permintaan transfer?"
                body={`Transfer ${transfer.id} akan dibatalkan. Ini hanya bisa dilakukan sebelum barang dikirim.`}
                confirmLabel="Batalkan"
                processing={processing}
                onConfirm={() => {
                    act('batalkan');
                    setCancelling(false);
                }}
                onCancel={() => setCancelling(false)}
            />
        </AdminLayout>
    );
}
