import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import Badge from './Badge';
import Card from './Card';
import Table from './Table';
import TableToolbar from './TableToolbar';
import { money, statusTone } from './data';

/**
 * Search + table screen for the straightforward admin lists.
 *
 * `columns` entries may carry `format: 'money' | 'status'` to get the shared
 * rendering; anything more specific should use `renderCell` or build the page
 * by hand instead.
 *
 * @param {{
 *   title: string,
 *   heading: string,
 *   breadcrumb: object[],
 *   columns: object[],
 *   rows: object[],
 *   rowKey: (row: object) => string,
 *   searchKeys: string[],
 *   placeholder?: string,
 *   actions?: import('react').ReactNode,
 *   renderCell?: (row: object, key: string) => import('react').ReactNode,
 *   empty?: string,
 * }} props
 */
export default function ListPage({
    title,
    heading,
    breadcrumb,
    columns,
    rows,
    rowKey,
    searchKeys,
    placeholder = 'Cari...',
    actions,
    renderCell,
    empty = 'Data tidak ditemukan.',
}) {
    const [search, setSearch] = useState('');

    const visible = rows.filter((row) =>
        searchKeys.some((key) =>
            String(row[key] ?? '').toLowerCase().includes(search.toLowerCase()),
        ),
    );

    const byFormat = (row, key) => {
        const column = columns.find((item) => item.key === key);

        if (column?.format === 'money') {
            return (
                <span className="font-semibold text-admin-heading dark:text-admin-dark-heading">
                    {money(row[key])}
                </span>
            );
        }

        if (column?.format === 'status') {
            return <Badge tone={statusTone(row[key])}>{row[key]}</Badge>;
        }

        return row[key];
    };

    return (
        <AdminLayout title={title} heading={heading} breadcrumb={breadcrumb} actions={actions}>
            <Card bodyClassName="p-0">
                <TableToolbar search={search} onSearch={setSearch} placeholder={placeholder} />

                <Table
                    columns={columns}
                    rows={visible}
                    rowKey={rowKey}
                    empty={empty}
                    renderCell={(row, key) => renderCell?.(row, key) ?? byFormat(row, key)}
                />
            </Card>
        </AdminLayout>
    );
}
