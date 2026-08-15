import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import StatCard from '@/Components/Admin/StatCard';
import { activityTimeline, img, money } from '@/Components/Admin/data';

const details = [
    { label: 'Email', value: 'kirana.wijaya@inofarma.co.id', icon: 'solar:letter-broken' },
    { label: 'Telepon', value: '+62 812-3456-7890', icon: 'solar:phone-broken' },
    { label: 'Kota', value: 'Jakarta Selatan', icon: 'solar:city-broken' },
    { label: 'Bergabung', value: '12 Januari 2024', icon: 'solar:clock-circle-broken' },
];

export default function Profile() {
    return (
        <AdminLayout
            title="Profil"
            heading="Profil Saya"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Profil' }]}
            actions={
                <Button href="/admin/pengaturan" size="sm" icon="solar:pen-2-broken">
                    Ubah Profil
                </Button>
            }
        >
            <div className="grid gap-5 lg:grid-cols-3">
                <Card>
                    <div className="text-center">
                        <img
                            src={img.user(1)}
                            alt="Kirana Wijaya"
                            className="mx-auto h-24 w-24 rounded-full object-cover"
                        />
                        <h2 className="mt-3 text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                            Kirana Wijaya
                        </h2>
                        <p className="text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            Apoteker Penanggung Jawab
                        </p>
                        <Badge tone="success" className="mt-2">
                            Aktif
                        </Badge>
                    </div>

                    <dl className="mt-6 space-y-4 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                        {details.map((detail) => (
                            <div key={detail.label} className="flex items-start gap-3">
                                <Icon name={detail.icon} size={18} className="mt-0.5 shrink-0 text-admin-muted" />
                                <div className="min-w-0">
                                    <dt className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {detail.label}
                                    </dt>
                                    <dd className="truncate text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {detail.value}
                                    </dd>
                                </div>
                            </div>
                        ))}
                    </dl>
                </Card>

                <div className="space-y-5 lg:col-span-2">
                    <div className="grid gap-4 sm:grid-cols-3">
                        <StatCard label="Pesanan Diproses" value="248" icon="solar:bag-smile-bold-duotone" />
                        <StatCard label="Produk Dikelola" value="132" icon="solar:box-bold-duotone" />
                        <StatCard label="Nilai Transaksi" value={money(486200000)} icon="solar:wallet-money-bold-duotone" />
                    </div>

                    <Card title="Aktivitas Terbaru">
                        <ol className="relative space-y-5 border-l border-admin-border pl-6 dark:border-admin-dark-border">
                            {activityTimeline.flatMap((group) =>
                                group.items.map((item) => (
                                    <li key={`${group.date}-${item.title}`} className="relative">
                                        <span className="absolute -left-[31px] flex h-5 w-5 items-center justify-center rounded-full bg-brand text-white">
                                            <Icon name={item.icon} size={11} />
                                        </span>

                                        <p className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {item.title}
                                        </p>
                                        <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                                            {group.date} · {item.time}
                                        </p>
                                        <p className="mt-1 text-[13px] leading-relaxed text-admin-body dark:text-admin-dark-body">
                                            {item.body}
                                        </p>
                                    </li>
                                )),
                            )}
                        </ol>
                    </Card>
                </div>
            </div>
        </AdminLayout>
    );
}
