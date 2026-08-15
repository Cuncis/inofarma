import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Card from '@/Components/Admin/Card';
import Icon from '@/Components/Admin/Icon';
import { chatMessages, conversations } from '@/Components/Admin/data';

export default function Chat() {
    const [active, setActive] = useState(conversations[0].name);
    const [messages, setMessages] = useState(chatMessages);
    const [draft, setDraft] = useState('');

    const send = (event) => {
        event.preventDefault();

        if (! draft.trim()) {
            return;
        }

        setMessages((current) => [
            ...current,
            {
                from: 'me',
                body: draft.trim(),
                at: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
            },
        ]);

        setDraft('');
    };

    return (
        <AdminLayout
            title="Chat"
            heading="Chat"
            breadcrumb={[{ label: 'Inofarma', href: '/admin' }, { label: 'Chat' }]}
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <Card bodyClassName="p-0" className="lg:col-span-1">
                    <div className="border-b border-admin-border p-4 dark:border-admin-dark-border">
                        <div className="relative">
                            <Icon
                                name="solar:magnifer-linear"
                                size={16}
                                className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-admin-muted"
                            />
                            <input
                                type="search"
                                placeholder="Cari percakapan..."
                                className="h-10 w-full rounded-lg border border-admin-border bg-admin-card pl-9 pr-3 text-[13px] text-admin-body placeholder:text-admin-muted focus:border-brand focus:outline-none focus:ring-0 dark:border-admin-dark-border dark:bg-admin-dark-card dark:text-admin-dark-body"
                            />
                        </div>
                    </div>

                    <ul className="max-h-[520px] divide-y divide-admin-border overflow-y-auto dark:divide-admin-dark-border">
                        {conversations.map((item) => (
                            <li key={item.name}>
                                <button
                                    type="button"
                                    onClick={() => setActive(item.name)}
                                    className={`flex w-full items-center gap-3 px-4 py-3 text-left transition-colors ${
                                        active === item.name
                                            ? 'bg-blush dark:bg-brand/20'
                                            : 'hover:bg-admin-hover dark:hover:bg-admin-dark-hover'
                                    }`}
                                >
                                    <span className="relative shrink-0">
                                        <img src={item.avatar} alt="" className="h-10 w-10 rounded-full object-cover" />
                                        {item.online ? (
                                            <span className="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-admin-card bg-success dark:border-admin-dark-card" />
                                        ) : null}
                                    </span>

                                    <span className="min-w-0 flex-1">
                                        <span className="block truncate text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                            {item.name}
                                        </span>
                                        <span className="block truncate text-xs text-admin-muted dark:text-admin-dark-muted">
                                            {item.last}
                                        </span>
                                    </span>

                                    <span className="shrink-0 text-right">
                                        <span className="block text-[10px] text-admin-muted dark:text-admin-dark-muted">
                                            {item.at}
                                        </span>
                                        {item.unread ? (
                                            <span className="mt-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[10px] font-bold text-white">
                                                {item.unread}
                                            </span>
                                        ) : null}
                                    </span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </Card>

                <Card bodyClassName="p-0" className="flex flex-col lg:col-span-2">
                    <div className="flex items-center gap-3 border-b border-admin-border p-4 dark:border-admin-dark-border">
                        <img
                            src={conversations.find((item) => item.name === active)?.avatar}
                            alt=""
                            className="h-10 w-10 rounded-full object-cover"
                        />
                        <div>
                            <p className="text-[13px] font-semibold text-admin-heading dark:text-admin-dark-heading">
                                {active}
                            </p>
                            <p className="text-xs text-admin-muted dark:text-admin-dark-muted">Online</p>
                        </div>
                    </div>

                    <div className="flex-1 space-y-3 overflow-y-auto p-4" style={{ maxHeight: 420 }}>
                        {messages.map((message, index) => (
                            <div
                                key={index}
                                className={`flex ${message.from === 'me' ? 'justify-end' : 'justify-start'}`}
                            >
                                <div
                                    className={`max-w-[75%] rounded-xl px-3.5 py-2.5 ${
                                        message.from === 'me'
                                            ? 'bg-brand text-white'
                                            : 'bg-admin-hover text-admin-body dark:bg-admin-dark-hover dark:text-admin-dark-body'
                                    }`}
                                >
                                    <p className="text-[13px] leading-relaxed">{message.body}</p>
                                    <p
                                        className={`mt-1 text-[10px] ${
                                            message.from === 'me' ? 'text-white/70' : 'text-admin-muted'
                                        }`}
                                    >
                                        {message.at}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>

                    <form onSubmit={send} className="flex gap-2 border-t border-admin-border p-4 dark:border-admin-dark-border">
                        <input
                            value={draft}
                            onChange={(event) => setDraft(event.target.value)}
                            placeholder="Tulis pesan..."
                            aria-label="Tulis pesan"
                            className="h-10 flex-1 rounded-lg border border-admin-border bg-admin-card px-3 text-[13px] text-admin-body placeholder:text-admin-muted focus:border-brand focus:outline-none focus:ring-0 dark:border-admin-dark-border dark:bg-admin-dark-card dark:text-admin-dark-body"
                        />
                        <button
                            type="submit"
                            aria-label="Kirim pesan"
                            className="flex h-10 w-10 items-center justify-center rounded-lg bg-brand text-white"
                        >
                            <Icon name="solar:plain-2-broken" size={18} />
                        </button>
                    </form>
                </Card>
            </div>
        </AdminLayout>
    );
}
