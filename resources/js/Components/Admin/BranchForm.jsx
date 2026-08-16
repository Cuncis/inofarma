import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Switch } from './Form';

/**
 * Create/update form for a branch, shared by the add and edit screens.
 *
 * Coordinates can be left blank — a branch with no `latitude`/`longitude` is
 * simply skipped by "nearest branch" everywhere else, rather than sorting to a
 * wrong point on the map. Weekly opening hours aren't editable here yet (a
 * known gap); new branches get a sensible default and existing ones keep
 * whatever they were seeded or last set with.
 *
 * @param {{ branch?: object, statuses: string[], submitLabel: string }} props
 */
export default function BranchForm({ branch, statuses, submitLabel }) {
    const editing = Boolean(branch);

    const { data, setData, post, put, processing, errors } = useForm({
        name: branch?.name ?? '',
        addressLine: branch?.addressLine ?? '',
        kelurahan: branch?.kelurahan ?? '',
        kecamatan: branch?.kecamatan ?? '',
        kota: branch?.kota ?? '',
        provinsi: branch?.provinsi ?? '',
        postalCode: branch?.postalCode ?? '',
        latitude: branch?.latitude ?? '',
        longitude: branch?.longitude ?? '',
        phone: branch?.phone ?? '',
        whatsapp: branch?.whatsapp ?? '',
        siaNumber: branch?.siaNumber ?? '',
        apjName: branch?.apjName ?? '',
        apjSipaNumber: branch?.apjSipaNumber ?? '',
        supportsDelivery: branch?.supportsDelivery ?? true,
        supportsPickup: branch?.supportsPickup ?? true,
        deliveryRadiusKm: branch?.deliveryRadiusKm ?? 10,
        status: branch?.status ?? statuses[0],
    });

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/cabang/${branch.id}`, options);
        } else {
            post('/admin/cabang', options);
        }
    };

    const field = (name, label, extra = {}) => (
        <Field label={label} htmlFor={name} hint={errors[name] ?? extra.hint} className={extra.full ? 'sm:col-span-2' : ''}>
            <Input
                id={name}
                type={extra.type ?? 'text'}
                value={data[name]}
                onChange={(event) => setData(name, event.target.value)}
                placeholder={extra.placeholder}
                aria-invalid={Boolean(errors[name])}
                className={errors[name] ? 'border-danger' : ''}
            />
        </Field>
    );

    return (
        <form onSubmit={submit} className="grid gap-5 lg:grid-cols-3">
            <div className="space-y-5 lg:col-span-2">
                <Card title="Identitas Cabang">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {field('name', 'Nama Cabang', { full: true, placeholder: 'Apotek Inofarma Cinere' })}

                        <Field label="SKU" htmlFor="code" hint="Dibuat otomatis">
                            <Input id="code" value={branch?.id ?? 'Otomatis'} disabled readOnly />
                        </Field>

                        <Field label="Status" htmlFor="status" hint={errors.status}>
                            <Select
                                id="status"
                                value={data.status}
                                onChange={(event) => setData('status', event.target.value)}
                                options={statuses}
                            />
                        </Field>

                        {field('phone', 'Telepon', { type: 'tel', placeholder: '+62 21 5551 0001' })}
                        {field('whatsapp', 'WhatsApp', { type: 'tel', placeholder: '+62 812-0000-1111' })}
                    </div>
                </Card>

                <Card title="Alamat & Lokasi">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {field('addressLine', 'Alamat Jalan', { full: true, placeholder: 'Jl. Contoh No. 1' })}
                        {field('kelurahan', 'Kelurahan')}
                        {field('kecamatan', 'Kecamatan')}
                        {field('kota', 'Kota/Kabupaten')}
                        {field('provinsi', 'Provinsi')}
                        {field('postalCode', 'Kode Pos')}

                        <div className="hidden sm:block" />

                        {field('latitude', 'Lintang (Latitude)', {
                            type: 'number',
                            placeholder: '-6.200000',
                            hint: errors.latitude ?? 'Kosongkan bila belum diketahui — cabang tidak akan muncul di pencarian terdekat.',
                        })}
                        {field('longitude', 'Bujur (Longitude)', { type: 'number', placeholder: '106.816666' })}
                    </div>
                </Card>

                <Card title="Perizinan">
                    <div className="grid gap-4 sm:grid-cols-2">
                        {field('siaNumber', 'Nomor SIA', { placeholder: 'SIA/2025/00123' })}
                        {field('apjName', 'Nama APJ')}
                        {field('apjSipaNumber', 'Nomor SIPA APJ')}
                    </div>
                </Card>
            </div>

            <div className="space-y-5">
                <Card title="Layanan">
                    <div className="space-y-4">
                        <Switch
                            checked={data.supportsDelivery}
                            onChange={() => setData('supportsDelivery', ! data.supportsDelivery)}
                            label="Melayani antar"
                        />
                        <Switch
                            checked={data.supportsPickup}
                            onChange={() => setData('supportsPickup', ! data.supportsPickup)}
                            label="Melayani ambil di tempat"
                        />

                        {field('deliveryRadiusKm', 'Radius Antar (km)', { type: 'number' })}
                    </div>
                </Card>

                <div className="flex gap-2">
                    <Button type="submit" disabled={processing} className="flex-1">
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/cabang" variant="outline">
                        Batal
                    </Button>
                </div>
            </div>
        </form>
    );
}
