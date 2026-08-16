import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import StatCard from '@/Components/Admin/StatCard';
import { statusTone } from '@/Components/Admin/data';

const DAY_LABELS = {
    senin: 'Senin',
    selasa: 'Selasa',
    rabu: 'Rabu',
    kamis: 'Kamis',
    jumat: 'Jumat',
    sabtu: 'Sabtu',
    minggu: 'Minggu',
};

export default function BranchDetail({ branch }) {
    const hours = branch.operatingHours ?? {};

    return (
        <AdminLayout
            title={branch.name}
            heading={branch.name}
            breadcrumb={[
                { label: 'Inofarma', href: '/admin' },
                { label: 'Cabang', href: '/admin/cabang' },
                { label: branch.name },
            ]}
            actions={
                <>
                    <Button
                        href={`/admin/inventaris/stok/${branch.id}`}
                        variant="outline"
                        size="sm"
                        icon="solar:box-bold-duotone"
                    >
                        Lihat Stok
                    </Button>
                    <Button href={`/admin/cabang/${branch.id}/ubah`} size="sm" icon="solar:pen-2-broken">
                        Ubah
                    </Button>
                </>
            }
        >
            <div className="mb-5 grid gap-4 sm:grid-cols-3">
                <StatCard label="Produk Distok" value={String(branch.stockCount)} icon="solar:box-bold-duotone" />
                <StatCard label="Staf Cabang" value={String(branch.staffCount)} icon="solar:users-group-two-rounded-bold-duotone" />
                <StatCard label="Radius Antar" value={`${branch.deliveryRadiusKm} km`} icon="solar:point-on-map-bold-duotone" />
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
                <Card title="Informasi Cabang" className="lg:col-span-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge tone={statusTone(branch.status)}>{branch.status}</Badge>
                        <Badge tone={branch.isOpenNow ? 'success' : 'neutral'}>
                            {branch.isOpenNow ? 'Buka sekarang' : 'Tutup sekarang'}
                        </Badge>
                        {branch.supportsDelivery ? <Badge tone="info">Antar</Badge> : null}
                        {branch.supportsPickup ? <Badge tone="info">Ambil di Tempat</Badge> : null}
                    </div>

                    <dl className="mt-4 space-y-3 border-t border-admin-border pt-4 text-[13px] dark:border-admin-dark-border">
                        <div className="flex justify-between gap-4">
                            <dt className="shrink-0 text-admin-muted dark:text-admin-dark-muted">Alamat</dt>
                            <dd className="text-right font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.fullAddress}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Koordinat</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.latitude && branch.longitude ? (
                                    <a href={branch.mapsUrl} target="_blank" rel="noreferrer" className="underline">
                                        {branch.latitude}, {branch.longitude}
                                    </a>
                                ) : (
                                    <span className="text-warning-deep">Belum diisi</span>
                                )}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Telepon</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.phone || '—'}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">WhatsApp</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.whatsapp || '—'}
                            </dd>
                        </div>
                    </dl>

                    <dl className="mt-4 space-y-3 border-t border-admin-border pt-4 text-[13px] dark:border-admin-dark-border">
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Nomor SIA</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.siaNumber || '—'}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Apoteker Penanggung Jawab</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.apjName || '—'}
                            </dd>
                        </div>
                        <div className="flex justify-between">
                            <dt className="text-admin-muted dark:text-admin-dark-muted">Nomor SIPA APJ</dt>
                            <dd className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                {branch.apjSipaNumber || '—'}
                            </dd>
                        </div>
                    </dl>
                </Card>

                <Card title="Jam Buka">
                    <ul className="space-y-2 text-[13px]">
                        {Object.entries(DAY_LABELS).map(([key, label]) => (
                            <li key={key} className="flex items-center justify-between">
                                <span className="text-admin-muted dark:text-admin-dark-muted">{label}</span>
                                {hours[key] ? (
                                    <span className="font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {hours[key].open} – {hours[key].close}
                                    </span>
                                ) : (
                                    <span className="text-admin-muted dark:text-admin-dark-muted">Tutup</span>
                                )}
                            </li>
                        ))}
                    </ul>

                    <p className="mt-4 flex items-start gap-2 rounded-lg bg-admin-hover px-3 py-2.5 text-xs leading-relaxed text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body">
                        <Icon name="solar:question-circle-bold-duotone" size={15} className="mt-0.5 shrink-0" />
                        Jam buka belum bisa diubah dari layar ini — hubungi pengembang untuk perubahan jadwal mingguan.
                    </p>
                </Card>
            </div>
        </AdminLayout>
    );
}
