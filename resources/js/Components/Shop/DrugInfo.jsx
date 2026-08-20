/**
 * The Indonesian pharmacy classification mark (logo golongan obat) and the
 * regulatory info block shown on a product's detail page — NIE, composition,
 * dosage, side effects, and the P1–P6 warning that is mandatory for
 * "Bebas Terbatas" products (Fase 4.2 / 9.3).
 *
 * Rendered as plain colour + text rather than the official BPOM artwork:
 * there is no licensed copy of that artwork in this repo to self-host, and
 * the circle + label convention (green/blue/red) is the same information a
 * shopper actually reads off the mark.
 */
const CLASS_STYLE = {
    Bebas: { ring: 'border-[#2fa84f] bg-[#2fa84f]', label: 'Obat Bebas' },
    'Bebas Terbatas': { ring: 'border-ink bg-[#1e6fd9]', label: 'Obat Bebas Terbatas' },
    Keras: { ring: 'border-[#d61f26] bg-white', label: 'Obat Keras', mark: 'K' },
};

/**
 * @param {{ drugClass?: string }} props
 */
export function DrugClassBadge({ drugClass }) {
    const style = CLASS_STYLE[drugClass];

    if (! style) {
        return null;
    }

    return (
        <div className="flex items-center gap-1.5">
            <span
                className={`flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 text-[10px] font-bold ${style.ring} ${style.mark ? 'text-[#d61f26]' : 'text-white'}`}
            >
                {style.mark ?? ''}
            </span>
            <span className="text-[11px] font-semibold text-ink">{style.label}</span>
        </div>
    );
}

/**
 * @param {{ product: import('@/lib/catalog').CatalogProduct }} props
 */
export function DrugInfoSection({ product }) {
    const rows = [
        ['Komposisi', product.composition],
        ['Indikasi', product.indication],
        ['Aturan Pakai', product.dosage],
        ['Efek Samping', product.sideEffects],
        ['Produsen', product.manufacturer],
        ['Nomor Izin Edar (NIE)', product.nie],
        ['Kondisi Penyimpanan', product.storage],
        ['Batas Pembelian', product.maxQtyPerOrder ? `${product.maxQtyPerOrder} per transaksi` : null],
    ].filter(([, value]) => Boolean(value));

    if (! rows.length && ! product.needsWarningLabel) {
        return null;
    }

    return (
        <div className="mb-3.5 space-y-3 rounded-lg border border-line bg-white p-3.5">
            <div className="flex items-center justify-between">
                <span className="text-[13px] font-bold">Informasi Obat</span>
                <DrugClassBadge drugClass={product.drugClass} />
            </div>

            {product.needsWarningLabel && product.warning ? (
                <div className="border-2 border-ink bg-[#eaf2fc] px-2.5 py-2 text-[11px] font-semibold leading-relaxed text-ink">
                    {product.warning}
                </div>
            ) : null}

            {rows.length ? (
                <dl className="space-y-1.5">
                    {rows.map(([label, value]) => (
                        <div key={label} className="flex gap-2 text-[11px] leading-relaxed">
                            <dt className="w-[38%] shrink-0 text-faint">{label}</dt>
                            <dd className="text-muted">{value}</dd>
                        </div>
                    ))}
                </dl>
            ) : null}

            <p className="text-[10px] italic text-faint">
                Informasi ini bukan pengganti nasihat medis. Hubungi apoteker cabang untuk
                pertanyaan lebih lanjut.
            </p>
        </div>
    );
}
