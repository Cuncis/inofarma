import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { calendarEvents } from '@/Components/Admin/data';

const weekdays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

/** @type {Record<string, string>} */
const tones = {
    brand: 'bg-blush text-brand dark:bg-brand/25 dark:text-white',
    success: 'bg-[#e8f9e9] text-success-deep dark:bg-success/25 dark:text-success',
    warning: 'bg-[#fff1e3] text-warning-deep dark:bg-warning/25 dark:text-warning',
    danger: 'bg-[#fdecec] text-danger-deep dark:bg-danger/25 dark:text-danger',
    info: 'bg-[#e6f7f5] text-info-deep dark:bg-info/25 dark:text-info',
};

/**
 * Weekday index of the 1st, shifted so Monday is column 0.
 *
 * @param {number} year
 * @param {number} month
 * @returns {number}
 */
function leadingBlanks(year, month) {
    return (new Date(year, month, 1).getDay() + 6) % 7;
}

export default function Calendar() {
    const [cursor, setCursor] = useState({ year: 2025, month: 7 });

    const daysInMonth = new Date(cursor.year, cursor.month + 1, 0).getDate();
    const blanks = leadingBlanks(cursor.year, cursor.month);
    const cells = [...Array(blanks).fill(null), ...Array.from({ length: daysInMonth }, (_, i) => i + 1)];

    const shift = (delta) =>
        setCursor((current) => {
            const next = current.month + delta;

            return {
                year: current.year + Math.floor(next / 12),
                month: ((next % 12) + 12) % 12,
            };
        });

    return (
        <AdminLayout
            title="Kalender"
            heading="Kalender"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Kalender' }]}
            actions={
                <Button size="sm" icon="solar:add-circle-broken">
                    Tambah Agenda
                </Button>
            }
        >
            <div className="grid gap-4 lg:grid-cols-4">
                <Card bodyClassName="p-0" className="lg:col-span-3">
                    <div className="flex items-center justify-between border-b border-admin-border px-5 py-4 dark:border-admin-dark-border">
                        <h2 className="text-[15px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                            {months[cursor.month]} {cursor.year}
                        </h2>

                        <div className="flex gap-1">
                            <button
                                type="button"
                                onClick={() => shift(-1)}
                                aria-label="Bulan sebelumnya"
                                className="flex h-8 w-8 items-center justify-center rounded-md border border-admin-border text-admin-body hover:bg-admin-hover dark:border-admin-dark-border dark:text-admin-dark-body dark:hover:bg-admin-dark-hover"
                            >
                                <Icon name="solar:alt-arrow-right-bold-duotone" size={14} className="rotate-180" />
                            </button>
                            <button
                                type="button"
                                onClick={() => shift(1)}
                                aria-label="Bulan berikutnya"
                                className="flex h-8 w-8 items-center justify-center rounded-md border border-admin-border text-admin-body hover:bg-admin-hover dark:border-admin-dark-border dark:text-admin-dark-body dark:hover:bg-admin-dark-hover"
                            >
                                <Icon name="solar:alt-arrow-right-bold-duotone" size={14} />
                            </button>
                        </div>
                    </div>

                    <div className="grid grid-cols-7 border-b border-admin-border dark:border-admin-dark-border">
                        {weekdays.map((day) => (
                            <div
                                key={day}
                                className="py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted"
                            >
                                {day}
                            </div>
                        ))}
                    </div>

                    <div className="grid grid-cols-7">
                        {cells.map((day, index) => {
                            const events = day
                                ? calendarEvents.filter((event) => event.day === day)
                                : [];

                            return (
                                <div
                                    key={index}
                                    className="min-h-[92px] border-b border-r border-admin-border p-1.5 last:border-r-0 dark:border-admin-dark-border"
                                >
                                    {day ? (
                                        <>
                                            <span className="text-xs font-medium text-admin-heading dark:text-admin-dark-heading">
                                                {day}
                                            </span>

                                            <div className="mt-1 space-y-1">
                                                {events.map((event) => (
                                                    <p
                                                        key={event.title}
                                                        className={`truncate rounded px-1.5 py-0.5 text-[10px] font-medium ${tones[event.tone]}`}
                                                        title={event.title}
                                                    >
                                                        {event.title}
                                                    </p>
                                                ))}
                                            </div>
                                        </>
                                    ) : null}
                                </div>
                            );
                        })}
                    </div>
                </Card>

                <Card title="Agenda Bulan Ini">
                    <ul className="space-y-3">
                        {calendarEvents.map((event) => (
                            <li key={event.title} className="flex gap-3">
                                <span className="flex h-9 w-9 shrink-0 flex-col items-center justify-center rounded-lg bg-admin-hover text-[11px] font-bold text-admin-heading dark:bg-admin-dark-hover dark:text-admin-dark-heading">
                                    {event.day}
                                </span>

                                <span className="min-w-0">
                                    <span className="block text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                        {event.title}
                                    </span>
                                    <Badge tone={event.tone === 'brand' ? 'brand' : event.tone} className="mt-1">
                                        {months[cursor.month]}
                                    </Badge>
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>
        </AdminLayout>
    );
}
