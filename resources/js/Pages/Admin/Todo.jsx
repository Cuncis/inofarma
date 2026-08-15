import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from '@/Components/Admin/Badge';
import Button from '@/Components/Admin/Button';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { Input } from '@/Components/Admin/Form';
import { todoItems } from '@/Components/Admin/data';

/** @type {Record<string, string>} */
const priorityTone = { Tinggi: 'danger', Sedang: 'warning', Rendah: 'neutral' };

const filters = ['Semua', 'Aktif', 'Selesai'];

export default function Todo() {
    const [items, setItems] = useState(todoItems);
    const [filter, setFilter] = useState('Semua');
    const [draft, setDraft] = useState('');

    const toggle = (id) =>
        setItems((current) =>
            current.map((item) => (item.id === id ? { ...item, done: ! item.done } : item)),
        );

    const add = (event) => {
        event.preventDefault();

        if (! draft.trim()) {
            return;
        }

        setItems((current) => [
            {
                id: Math.max(0, ...current.map((item) => item.id)) + 1,
                title: draft.trim(),
                due: 'Hari ini',
                priority: 'Sedang',
                done: false,
            },
            ...current,
        ]);

        setDraft('');
    };

    const visible = items.filter((item) =>
        filter === 'Semua' ? true : filter === 'Selesai' ? item.done : ! item.done,
    );

    return (
        <AdminLayout
            title="Todo"
            heading="Daftar Tugas"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Todo' }]}
        >
            <div className="mx-auto max-w-3xl">
                <Card bodyClassName="p-0">
                    <form onSubmit={add} className="flex gap-2 border-b border-admin-border p-4 dark:border-admin-dark-border">
                        <Input
                            value={draft}
                            onChange={(event) => setDraft(event.target.value)}
                            placeholder="Tambah tugas baru..."
                            aria-label="Tugas baru"
                            className="flex-1"
                        />
                        <Button type="submit" icon="solar:add-circle-broken">
                            Tambah
                        </Button>
                    </form>

                    <div className="flex gap-2 border-b border-admin-border px-4 py-3 dark:border-admin-dark-border">
                        {filters.map((name) => (
                            <button
                                key={name}
                                type="button"
                                onClick={() => setFilter(name)}
                                className={`rounded-md px-3 py-1.5 text-xs font-semibold transition-colors ${
                                    filter === name
                                        ? 'bg-brand text-white'
                                        : 'text-admin-body hover:bg-admin-hover dark:text-admin-dark-body dark:hover:bg-admin-dark-hover'
                                }`}
                            >
                                {name}
                            </button>
                        ))}

                        <span className="ml-auto self-center text-xs text-admin-muted dark:text-admin-dark-muted">
                            {items.filter((item) => ! item.done).length} tersisa
                        </span>
                    </div>

                    {visible.length === 0 ? (
                        <p className="px-5 py-10 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            Tidak ada tugas pada filter ini.
                        </p>
                    ) : (
                        <ul className="divide-y divide-admin-border dark:divide-admin-dark-border">
                            {visible.map((item) => (
                                <li key={item.id} className="flex items-center gap-3 px-5 py-3.5">
                                    <input
                                        type="checkbox"
                                        checked={item.done}
                                        onChange={() => toggle(item.id)}
                                        aria-label={item.title}
                                        className="h-4 w-4 shrink-0 rounded border-admin-border text-brand focus:ring-brand dark:border-admin-dark-border"
                                    />

                                    <span className="min-w-0 flex-1">
                                        <span
                                            className={`block text-[13px] ${
                                                item.done
                                                    ? 'text-admin-muted line-through dark:text-admin-dark-muted'
                                                    : 'font-medium text-admin-heading dark:text-admin-dark-heading'
                                            }`}
                                        >
                                            {item.title}
                                        </span>
                                        <span className="mt-0.5 flex items-center gap-1 text-xs text-admin-muted dark:text-admin-dark-muted">
                                            <Icon name="solar:clock-circle-broken" size={12} />
                                            {item.due}
                                        </span>
                                    </span>

                                    <Badge tone={priorityTone[item.priority]}>{item.priority}</Badge>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            setItems((current) => current.filter((row) => row.id !== item.id))
                                        }
                                        aria-label={`Hapus ${item.title}`}
                                        className="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-danger hover:bg-admin-hover dark:hover:bg-admin-dark-hover"
                                    >
                                        <Icon name="solar:trash-bin-minimalistic-2-broken" size={16} />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </AdminLayout>
    );
}
