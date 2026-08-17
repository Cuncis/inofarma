import { useForm } from '@inertiajs/react';
import Button from './Button';
import Card from './Card';
import { Field, Input, Select, Switch } from './Form';

/**
 * Create/update form for a coupon, shared by the add and edit screens.
 *
 * `branches` starts unchecked for every branch, which means "applies
 * everywhere" — the same empty-means-all convention as `users.branch_id`.
 * Checking any branch narrows the coupon to just those.
 *
 * @param {{
 *   coupon?: object,
 *   types: string[],
 *   statuses: string[],
 *   branches: { code: string, name: string }[],
 *   submitLabel: string,
 * }} props
 */
export default function CouponForm({ coupon, types, statuses, branches, submitLabel }) {
    const editing = Boolean(coupon);

    const { data, setData, post, put, processing, errors } = useForm({
        code: coupon?.code ?? '',
        type: coupon?.type ?? types[0],
        value: coupon?.value ?? '',
        minimumPurchase: coupon?.minimumPurchase ?? '',
        quota: coupon?.quota ?? '',
        startsAt: coupon?.startsAt ?? '',
        expiresAt: coupon?.expiresAt ?? '',
        status: coupon?.status === 'Habis' || coupon?.status === 'Kedaluwarsa' ? 'Aktif' : coupon?.status ?? statuses[0],
        branches: coupon?.branches ?? [],
    });

    const isFreeShipping = data.type === 'Gratis Ongkir';

    const toggleBranch = (name) => {
        setData(
            'branches',
            data.branches.includes(name)
                ? data.branches.filter((item) => item !== name)
                : [...data.branches, name],
        );
    };

    const submit = (event) => {
        event.preventDefault();

        const options = { preserveScroll: true };

        if (editing) {
            put(`/admin/kupon/${coupon.id}`, options);
        } else {
            post('/admin/kupon', options);
        }
    };

    return (
        <form onSubmit={submit} className="mx-auto max-w-3xl">
            <Card title="Informasi Kupon">
                <div className="grid gap-4 sm:grid-cols-2">
                    <Field label="Kode Kupon" htmlFor="code" hint={errors.code ?? 'Huruf kapital dan angka saja.'}>
                        <Input
                            id="code"
                            value={data.code}
                            onChange={(event) => setData('code', event.target.value.toUpperCase())}
                            placeholder="HEMAT15"
                            className={errors.code ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Tipe Diskon" htmlFor="type" hint={errors.type}>
                        <Select
                            id="type"
                            value={data.type}
                            onChange={(event) => setData('type', event.target.value)}
                            options={types}
                        />
                    </Field>

                    {! isFreeShipping ? (
                        <Field
                            label={data.type === 'Persentase' ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)'}
                            htmlFor="value"
                            hint={errors.value}
                        >
                            <Input
                                id="value"
                                type="number"
                                min="0"
                                value={data.value}
                                onChange={(event) => setData('value', event.target.value)}
                                placeholder="15"
                                className={errors.value ? 'border-danger' : ''}
                            />
                        </Field>
                    ) : null}

                    <Field label="Minimum Belanja (Rp)" htmlFor="minimumPurchase" hint={errors.minimumPurchase ?? 'Kosongkan bila tidak ada.'}>
                        <Input
                            id="minimumPurchase"
                            type="number"
                            min="0"
                            value={data.minimumPurchase}
                            onChange={(event) => setData('minimumPurchase', event.target.value)}
                            placeholder="100000"
                            className={errors.minimumPurchase ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Kuota Penggunaan" htmlFor="quota" hint={errors.quota ?? 'Kosongkan bila tidak dibatasi.'}>
                        <Input
                            id="quota"
                            type="number"
                            min="1"
                            value={data.quota}
                            onChange={(event) => setData('quota', event.target.value)}
                            placeholder="500"
                            className={errors.quota ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Status" htmlFor="status" hint={errors.status}>
                        <Select
                            id="status"
                            value={data.status}
                            onChange={(event) => setData('status', event.target.value)}
                            options={statuses}
                        />
                    </Field>

                    <Field label="Mulai Berlaku" htmlFor="startsAt" hint={errors.startsAt}>
                        <Input
                            id="startsAt"
                            type="date"
                            value={data.startsAt}
                            onChange={(event) => setData('startsAt', event.target.value)}
                            className={errors.startsAt ? 'border-danger' : ''}
                        />
                    </Field>

                    <Field label="Berlaku Sampai" htmlFor="expiresAt" hint={errors.expiresAt}>
                        <Input
                            id="expiresAt"
                            type="date"
                            value={data.expiresAt}
                            onChange={(event) => setData('expiresAt', event.target.value)}
                            className={errors.expiresAt ? 'border-danger' : ''}
                        />
                    </Field>
                </div>

                <div className="mt-5 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <p className="mb-1 text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                        Berlaku di Cabang
                    </p>
                    <p className="mb-3 text-xs text-admin-muted dark:text-admin-dark-muted">
                        {errors.branches ?? 'Tidak mencentang cabang mana pun berarti berlaku di semua cabang.'}
                    </p>

                    <div className="grid gap-2 sm:grid-cols-2">
                        {branches.map((branch) => (
                            <label key={branch.code} className="flex items-center gap-2.5">
                                <Switch
                                    checked={data.branches.includes(branch.code)}
                                    onChange={() => toggleBranch(branch.code)}
                                    label={branch.name}
                                />
                            </label>
                        ))}
                    </div>
                </div>

                <div className="mt-6 flex gap-2 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Menyimpan…' : submitLabel}
                    </Button>
                    <Button href="/admin/kupon" variant="outline">
                        Batal
                    </Button>
                </div>
            </Card>
        </form>
    );
}
