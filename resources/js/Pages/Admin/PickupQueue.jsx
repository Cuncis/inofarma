import { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Table from '@/Components/Admin/Table';
import { money } from '@/Components/Admin/data';

/**
 * The branch counter's own screen (ROADMAP.md 7.2): every order currently
 * `siap diambil` at this staff member's branch, and the code-entry form that
 * hands one over. `prefill` comes from a customer's pickup QR — see
 * `App\Support\Pickup\PickupCodeService::qrSvgDataUri()` — the camera still
 * has to land the staff member on this exact page; nothing submits itself.
 *
 * @param {{
 *   orders: object[],
 *   prefill: { order: ?string, code: ?string },
 * }} props
 */
export default function PickupQueue({ orders, prefill }) {
    const [codes, setCodes] = useState({});

    useEffect(() => {
        if (prefill.order && prefill.code) {
            setCodes((current) => ({ ...current, [prefill.order]: prefill.code }));
        }
    }, [prefill.order, prefill.code]);

    const handOver = (orderId) => {
        router.post(
            `/admin/pengambilan/${orderId}/serahkan`,
            { code: codes[orderId] ?? '' },
            { preserveScroll: true },
        );
    };

    return (
        <AdminLayout
            title="Pengambilan"
            heading="Pengambilan di Cabang"
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Pengambilan' },
            ]}
        >
            <Card title="Siap Diambil" bodyClassName="p-0">
                <Table
                    columns={[
                        { key: 'id', label: 'No. Pesanan' },
                        { key: 'customerName', label: 'Pelanggan' },
                        { key: 'itemCount', label: 'Item', align: 'right' },
                        { key: 'total', label: 'Total', align: 'right' },
                        { key: 'code', label: 'Kode Ambil' },
                        { key: 'action', label: '' },
                    ]}
                    rows={orders}
                    rowKey={(row) => row.id}
                    renderCell={(row, key) => {
                        if (key === 'total') {
                            return money(row.total);
                        }

                        if (key === 'code') {
                            return (
                                <input
                                    type="text"
                                    inputMode="numeric"
                                    maxLength={10}
                                    value={codes[row.id] ?? ''}
                                    onChange={(event) =>
                                        setCodes((current) => ({ ...current, [row.id]: event.target.value }))
                                    }
                                    placeholder="123456"
                                    className="w-28 rounded-md border border-admin-border bg-transparent px-2 py-1 text-[13px] tracking-widest dark:border-admin-dark-border"
                                />
                            );
                        }

                        if (key === 'action') {
                            return (
                                <button
                                    type="button"
                                    onClick={() => handOver(row.id)}
                                    disabled={! (codes[row.id] ?? '').length}
                                    className="rounded-md bg-brand px-3 py-1.5 text-[13px] font-semibold text-white disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    Serahkan
                                </button>
                            );
                        }

                        return row[key];
                    }}
                    empty="Tidak ada pesanan yang siap diambil saat ini."
                />
            </Card>
        </AdminLayout>
    );
}
