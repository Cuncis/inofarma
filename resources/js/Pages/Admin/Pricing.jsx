import AdminLayout from '@/Layouts/AdminLayout';
import Button from '@/Components/Admin/Button';
import Icon from '@/Components/Admin/Icon';
import { money, pricingPlans } from '@/Components/Admin/data';

export default function Pricing() {
    return (
        <AdminLayout
            title="Paket Harga"
            heading="Paket Harga"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Harga' }]}
        >
            <div className="mx-auto mb-8 max-w-xl text-center">
                <h2 className="text-lg font-semibold text-admin-heading dark:text-admin-dark-heading">
                    Pilih paket yang sesuai
                </h2>
                <p className="mt-1.5 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                    Semua paket sudah termasuk pembaruan gratis dan dukungan teknis.
                </p>
            </div>

            <div className="grid gap-5 lg:grid-cols-3">
                {pricingPlans.map((plan) => (
                    <div
                        key={plan.name}
                        className={`rounded-xl border p-6 shadow-card ${
                            plan.highlight
                                ? 'border-brand bg-admin-card ring-2 ring-brand dark:bg-admin-dark-card'
                                : 'border-admin-border bg-admin-card dark:border-admin-dark-border dark:bg-admin-dark-card'
                        }`}
                    >
                        {plan.highlight ? (
                            <span className="mb-3 inline-flex rounded-md bg-brand px-2.5 py-1 text-[11px] font-bold text-white">
                                Paling Populer
                            </span>
                        ) : null}

                        <h3 className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {plan.name}
                        </h3>

                        <p className="mt-3">
                            <span className="text-3xl font-bold text-admin-heading dark:text-admin-dark-heading">
                                {plan.price === 0 ? 'Gratis' : money(plan.price)}
                            </span>
                            {plan.price > 0 ? (
                                <span className="text-[13px] text-admin-muted dark:text-admin-dark-muted">
                                    {' '}/ {plan.period}
                                </span>
                            ) : null}
                        </p>

                        <ul className="mt-5 space-y-2.5 border-t border-admin-border pt-5 dark:border-admin-dark-border">
                            {plan.features.map((feature) => (
                                <li
                                    key={feature}
                                    className="flex items-start gap-2 text-[13px] text-admin-body dark:text-admin-dark-body"
                                >
                                    <Icon
                                        name="solar:check-circle-broken"
                                        size={16}
                                        className="mt-0.5 shrink-0 text-success-deep"
                                    />
                                    {feature}
                                </li>
                            ))}
                        </ul>

                        <Button
                            variant={plan.highlight ? 'primary' : 'outline'}
                            className="mt-6 w-full"
                        >
                            {plan.price === 0 ? 'Mulai Gratis' : 'Pilih Paket'}
                        </Button>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
