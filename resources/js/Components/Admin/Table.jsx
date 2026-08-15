/**
 * Data table.
 *
 * Wide tables scroll inside their own container so the page body never scrolls
 * horizontally on small screens.
 *
 * @param {{
 *   columns: { key: string, label: string, align?: 'left'|'right'|'center', className?: string }[],
 *   rows: object[],
 *   renderCell: (row: object, key: string) => import('react').ReactNode,
 *   rowKey: (row: object) => string,
 *   empty?: string,
 * }} props
 */
export default function Table({ columns, rows, renderCell, rowKey, empty = 'Tidak ada data.' }) {
    const alignment = {
        right: 'text-right',
        center: 'text-center',
        left: 'text-left',
    };

    return (
        <div className="overflow-x-auto">
            <table className="w-full min-w-[640px] border-collapse text-left">
                <thead>
                    <tr className="border-b border-admin-border dark:border-admin-dark-border">
                        {columns.map((column) => (
                            <th
                                key={column.key}
                                scope="col"
                                className={`whitespace-nowrap px-4 py-3 text-[11px] font-bold uppercase tracking-wider text-admin-muted dark:text-admin-dark-muted ${
                                    alignment[column.align ?? 'left']
                                } ${column.className ?? ''}`}
                            >
                                {column.label}
                            </th>
                        ))}
                    </tr>
                </thead>

                <tbody>
                    {rows.length === 0 ? (
                        <tr>
                            <td
                                colSpan={columns.length}
                                className="px-4 py-10 text-center text-[13px] text-admin-muted dark:text-admin-dark-muted"
                            >
                                {empty}
                            </td>
                        </tr>
                    ) : (
                        rows.map((row) => (
                            <tr
                                key={rowKey(row)}
                                className="border-b border-admin-border last:border-0 hover:bg-admin-hover dark:border-admin-dark-border dark:hover:bg-admin-dark-hover"
                            >
                                {columns.map((column) => (
                                    <td
                                        key={column.key}
                                        className={`px-4 py-3 text-[13px] text-admin-body dark:text-admin-dark-body ${
                                            alignment[column.align ?? 'left']
                                        }`}
                                    >
                                        {renderCell(row, column.key)}
                                    </td>
                                ))}
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
