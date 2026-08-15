import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { emails as seed } from '@/Components/Admin/data';

const folders = [
    { key: 'masuk', label: 'Kotak Masuk', icon: 'solar:mailbox-bold-duotone' },
    { key: 'berbintang', label: 'Berbintang', icon: 'solar:star-bold' },
    { key: 'terkirim', label: 'Terkirim', icon: 'solar:plain-2-broken' },
    { key: 'sampah', label: 'Sampah', icon: 'solar:trash-bin-minimalistic-2-broken' },
];

export default function Email() {
    const [rows, setRows] = useState(seed);
    const [folder, setFolder] = useState('masuk');

    const toggleStar = (subject) =>
        setRows((current) =>
            current.map((row) => (row.subject === subject ? { ...row, starred: ! row.starred } : row)),
        );

    const visible = folder === 'berbintang' ? rows.filter((row) => row.starred) : rows;
    const unread = rows.filter((row) => row.unread).length;

    return (
        <AdminLayout
            title="Email"
            heading="Email"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Email' }]}
        >
            <div className="grid gap-4 lg:grid-cols-4">
                <Card bodyClassName="p-3" className="lg:col-span-1">
                    <ul className="space-y-1">
                        {folders.map((item) => (
                            <li key={item.key}>
                                <button
                                    type="button"
                                    onClick={() => setFolder(item.key)}
                                    className={`flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] transition-colors ${
                                        folder === item.key
                                            ? 'bg-brand text-white'
                                            : 'text-admin-body hover:bg-admin-hover dark:text-admin-dark-body dark:hover:bg-admin-dark-hover'
                                    }`}
                                >
                                    <Icon name={item.icon} size={18} />
                                    <span className="flex-1 text-left">{item.label}</span>
                                    {item.key === 'masuk' && unread ? (
                                        <span
                                            className={`rounded-full px-1.5 text-[10px] font-bold ${
                                                folder === 'masuk' ? 'bg-white/25' : 'bg-brand text-white'
                                            }`}
                                        >
                                            {unread}
                                        </span>
                                    ) : null}
                                </button>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card bodyClassName="p-0" className="lg:col-span-3">
                    {visible.length === 0 ? (
                        <p className="px-5 py-12 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted">
                            Tidak ada email di folder ini.
                        </p>
                    ) : (
                        <ul className="divide-y divide-admin-border dark:divide-admin-dark-border">
                            {visible.map((mail) => (
                                <li
                                    key={mail.subject}
                                    className={`flex items-center gap-3 px-4 py-3.5 hover:bg-admin-hover dark:hover:bg-admin-dark-hover ${
                                        mail.unread ? 'bg-blush/40 dark:bg-brand/10' : ''
                                    }`}
                                >
                                    <button
                                        type="button"
                                        onClick={() => toggleStar(mail.subject)}
                                        aria-label={mail.starred ? `Hapus bintang ${mail.subject}` : `Beri bintang ${mail.subject}`}
                                        className="shrink-0"
                                    >
                                        <Icon
                                            name="solar:star-bold"
                                            size={17}
                                            className={
                                                mail.starred
                                                    ? 'text-warning'
                                                    : 'text-admin-border dark:text-admin-dark-border'
                                            }
                                        />
                                    </button>

                                    <span className="w-40 shrink-0 truncate text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                        {mail.from}
                                    </span>

                                    <span className="min-w-0 flex-1">
                                        <span className="text-[13px] font-medium text-admin-heading dark:text-admin-dark-heading">
                                            {mail.subject}
                                        </span>
                                        <span className="ml-2 text-[13px] text-admin-muted dark:text-admin-dark-muted">
                                            — {mail.preview}
                                        </span>
                                    </span>

                                    <span className="shrink-0 text-xs text-admin-muted dark:text-admin-dark-muted">
                                        {mail.at}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    )}
                </Card>
            </div>
        </AdminLayout>
    );
}
