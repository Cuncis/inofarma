import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { productReviews, statusTone } from '@/Components/Admin/data';

const filters = ['Semua', 'Disetujui', 'Menunggu'];

export default function Reviews() {
    const [rows, setRows] = useState(productReviews);
    const [filter, setFilter] = useState('Semua');

    const visible = rows.filter((row) => filter === 'Semua' || row.status === filter);

    const approve = (author) =>
        setRows((current) =>
            current.map((row) => (row.author === author ? { ...row, status: 'Disetujui' } : row)),
        );

    return (
        <AdminLayout
            title="Ulasan"
            heading="Ulasan Produk"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Ulasan' }]}
        >
            <div className="mb-5 flex gap-2">
                {filters.map((name) => (
                    <button
                        key={name}
                        type="button"
                        onClick={() => setFilter(name)}
                        className={`rounded-lg px-4 py-2 text-[13px] font-semibold transition-colors ${
                            filter === name
                                ? 'bg-brand text-white'
                                : 'border border-admin-border text-admin-body hover:bg-admin-hover dark:border-admin-dark-border dark:text-admin-dark-body dark:hover:bg-admin-dark-hover'
                        }`}
                    >
                        {name}
                    </button>
                ))}
            </div>

            <div className="space-y-4">
                {visible.length === 0 ? (
                    <Card>
                        <p className="py-6 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            Tidak ada ulasan pada filter ini.
                        </p>
                    </Card>
                ) : (
                    visible.map((review) => (
                        <Card key={review.author}>
                            <div className="flex flex-wrap items-start gap-4">
                                <img
                                    src={review.avatar}
                                    alt=""
                                    className="h-11 w-11 shrink-0 rounded-full object-cover"
                                />

                                <div className="min-w-0 flex-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {review.author}
                                        </p>
                                        <Badge tone={statusTone(review.status)}>{review.status}</Badge>
                                    </div>

                                    <p className="mt-0.5 text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {review.product} · {review.date}
                                    </p>

                                    <div className="mt-2 flex gap-0.5">
                                        {[1, 2, 3, 4, 5].map((star) => (
                                            <Icon
                                                key={star}
                                                name="solar:star-bold"
                                                size={14}
                                                className={
                                                    star <= review.score
                                                        ? 'text-warning'
                                                        : 'text-admin-border dark:text-admin-dark-border'
                                                }
                                            />
                                        ))}
                                    </div>

                                    <p className="mt-2.5 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                        {review.body}
                                    </p>
                                </div>

                                {review.status === 'Menunggu' ? (
                                    <Button size="sm" onClick={() => approve(review.author)}>
                                        Setujui
                                    </Button>
                                ) : null}
                            </div>
                        </Card>
                    ))
                )}
            </div>
        </AdminLayout>
    );
}
