import { useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import Icon from './Icon';
import Button from './Button';
import useDragScroll from './useDragScroll';

/**
 * "Which apotek, and how do I get it" — the picker on the product page.
 *
 * Loads branches for this one product from the JSON locator API rather than a
 * prop, because the list has to re-sort the moment the browser's geolocation
 * prompt resolves, without a full Inertia page visit. A branch with no stock
 * still shows up — a shopper needs to know it exists — but cannot be selected.
 *
 * Adding to cart posts straight to `CartManager` (Fase 5.3). A cart is bound
 * to one branch (ROADMAP.md 3.3); if this cart already holds items from a
 * different branch, the server refuses with a `branch` error and this asks
 * whether to empty the cart and switch instead of failing silently.
 *
 * @param {{ productId: string, productName: string, maxQtyPerOrder: ?number }} props
 */
export default function BranchPicker({ productId, productName, maxQtyPerOrder }) {
    const [branches, setBranches] = useState(null);
    const [error, setError] = useState('');
    const [selected, setSelected] = useState(null);
    const [fulfilment, setFulfilment] = useState('antar');
    const [quantity, setQuantity] = useState(1);
    const [added, setAdded] = useState(false);
    const branchDrag = useDragScroll();

    const { transform, post, processing, errors, clearErrors } = useForm({});

    useEffect(() => {
        let cancelled = false;

        fetch(`/api/cabang/untuk-produk/${productId}`, {
            headers: { Accept: 'application/json' },
        })
            .then((response) => {
                if (! response.ok) {
                    throw new Error('gagal memuat');
                }

                return response.json();
            })
            .then((body) => {
                if (cancelled) {
                    return;
                }

                setBranches(body.branches);
                setSelected(body.branches.find((branch) => branch.selectable) ?? null);
            })
            .catch(() => {
                if (! cancelled) {
                    setError('Tidak bisa memuat daftar cabang. Coba lagi nanti.');
                }
            });

        return () => {
            cancelled = true;
        };
    }, [productId]);

    const pick = (branch) => {
        if (! branch.selectable) {
            return;
        }

        setSelected(branch);
        setQuantity(1);
        clearErrors();

        if (branch.supportsPickup && ! branch.supportsDelivery) {
            setFulfilment('ambil');
        } else if (branch.supportsDelivery && ! branch.supportsPickup) {
            setFulfilment('antar');
        }
    };

    const addToCart = (switchBranch = false) => {
        if (! selected) {
            return;
        }

        transform(() => ({ productId, branchId: selected.id, quantity, switchBranch }));

        post('/ui/keranjang', {
            preserveScroll: true,
            onSuccess: () => {
                setAdded(true);
                window.setTimeout(() => setAdded(false), 1800);
            },
        });
    };

    if (error) {
        return <p className="mb-3.5 text-xs text-danger">{error}</p>;
    }

    if (! branches) {
        return (
            <div className="mb-3.5 h-16 animate-pulse border border-line bg-[#f5f5f5]" aria-hidden="true" />
        );
    }

    return (
        <div className="mb-3.5">
            <div className="mb-[7px] flex items-center justify-between">
                <span className="text-[13px] font-bold">Pilih Cabang</span>
                <Link href="/ui/cabang-kami" className="text-[11px] text-brand">
                    Lihat semua cabang
                </Link>
            </div>

            <div
                {...branchDrag}
                className={`-mx-3.5 flex gap-2.5 overflow-x-auto px-3.5 pb-1 ${branchDrag.className}`}
            >
                {branches.map((branch) => (
                    <button
                        key={branch.id}
                        type="button"
                        onClick={() => pick(branch)}
                        disabled={! branch.selectable}
                        className={`w-[210px] shrink-0 border p-2.5 text-left ${
                            selected?.id === branch.id
                                ? 'border-2 border-brand'
                                : branch.selectable
                                  ? 'border-line'
                                  : 'border-line opacity-50'
                        }`}
                    >
                        <div className="mb-1 flex items-start justify-between gap-1.5">
                            <span className="text-xs font-bold leading-tight">{branch.name}</span>
                            {branch.distanceKm !== null ? (
                                <span className="shrink-0 whitespace-nowrap text-[10px] font-bold text-brand">
                                    {branch.distanceKm} km
                                </span>
                            ) : null}
                        </div>

                        <div className="mb-1.5 text-[10px] text-muted">{branch.kota}</div>

                        <div className="flex items-center justify-between text-[10px]">
                            <span
                                className={
                                    branch.selectable
                                        ? 'font-semibold text-success-deep'
                                        : 'font-semibold text-brand'
                                }
                            >
                                {branch.selectable ? `Stok ${branch.available}` : 'Stok kosong'}
                            </span>
                            <span className={branch.isOpenNow ? 'text-success-deep' : 'text-muted'}>
                                {branch.isOpenNow ? 'Buka' : 'Tutup'}
                            </span>
                        </div>
                    </button>
                ))}
            </div>

            {branches.length === 0 ? (
                <p className="text-xs text-muted">Belum ada cabang yang menjual produk ini.</p>
            ) : null}

            {selected ? (
                <>
                    <div className="mt-2.5 flex gap-[7px]">
                        {selected.supportsDelivery ? (
                            <button
                                type="button"
                                onClick={() => setFulfilment('antar')}
                                className={`flex flex-1 items-center justify-center gap-1.5 py-2 text-[11px] ${
                                    fulfilment === 'antar'
                                        ? 'border-2 border-brand font-bold text-brand'
                                        : 'border border-line text-muted'
                                }`}
                            >
                                <Icon name="bagSimple" size={14} />
                                Antar
                            </button>
                        ) : null}

                        {selected.supportsPickup ? (
                            <button
                                type="button"
                                onClick={() => setFulfilment('ambil')}
                                className={`flex flex-1 items-center justify-center gap-1.5 py-2 text-[11px] ${
                                    fulfilment === 'ambil'
                                        ? 'border-2 border-brand font-bold text-brand'
                                        : 'border border-line text-muted'
                                }`}
                            >
                                <Icon name="pin" size={14} />
                                Ambil di Tempat
                            </button>
                        ) : null}
                    </div>

                    <div className="mt-2.5 flex items-center justify-between">
                        <span className="text-[11px] text-muted">Jumlah</span>

                        <div className="flex items-center gap-2.5">
                            <button
                                type="button"
                                onClick={() => setQuantity((qty) => Math.max(1, qty - 1))}
                                aria-label={`Kurangi jumlah ${productName}`}
                                className="flex h-6 w-6 items-center justify-center border border-line text-sm leading-none"
                            >
                                −
                            </button>
                            <span className="min-w-[16px] text-center text-xs">{quantity}</span>
                            <button
                                type="button"
                                onClick={() =>
                                    setQuantity((qty) =>
                                        maxQtyPerOrder ? Math.min(maxQtyPerOrder, qty + 1) : qty + 1,
                                    )
                                }
                                aria-label={`Tambah jumlah ${productName}`}
                                className="flex h-6 w-6 items-center justify-center border border-line text-sm leading-none"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    {maxQtyPerOrder ? (
                        <p className="mt-1 text-right text-[10px] text-muted">
                            Maksimal {maxQtyPerOrder} per transaksi.
                        </p>
                    ) : null}

                    {errors.quantity ? (
                        <p className="mt-1.5 text-[11px] text-danger">{errors.quantity}</p>
                    ) : null}

                    {errors.branch ? (
                        <div className="mt-1.5 border border-danger bg-danger/5 p-2.5 text-[11px] text-danger-deep">
                            <p className="mb-1.5">{errors.branch}</p>
                            <button
                                type="button"
                                onClick={() => addToCart(true)}
                                disabled={processing}
                                className="font-bold underline"
                            >
                                Ya, kosongkan &amp; pindah
                            </button>
                        </div>
                    ) : (
                        <Button
                            type="button"
                            onClick={() => addToCart(false)}
                            disabled={processing}
                            className="mt-2.5"
                        >
                            {added ? 'Ditambahkan ✓' : 'Masukkan ke Keranjang'}
                        </Button>
                    )}
                </>
            ) : null}
        </div>
    );
}
