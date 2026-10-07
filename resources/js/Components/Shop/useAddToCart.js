import { useState } from 'react';
import { router } from '@inertiajs/react';
import flyToCart from './flyToCart';

/**
 * One-tap "Tambah" for a product card.
 *
 * A cart is bound to one branch, and the cards have no branch picker, so this
 * takes the first selectable branch from the locator API (the list is already
 * sorted nearest-first once the shopper has shared a location) and posts a
 * single unit to the same `/ui/keranjang` endpoint `BranchPicker` uses. When
 * the cart already holds another branch's items, the server's `branch` error
 * is put to the shopper as a confirm before the cart is switched.
 *
 * The header badge follows the shared `cartCount` prop, which refreshes with
 * the redirect that follows a successful add.
 *
 * @param {string} productId
 * @returns {{ add: (anchor: ?HTMLElement) => Promise<void>, processing: boolean, error: string }}
 */
export default function useAddToCart(productId) {
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState('');

    const submit = (branchId, switchBranch, anchor) => {
        router.post(
            '/ui/keranjang',
            { productId, branchId, quantity: 1, switchBranch },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => flyToCart(anchor),
                onError: (errors) => {
                    if (errors.branch) {
                        if (window.confirm(errors.branch)) {
                            submit(branchId, true, anchor);
                        }

                        return;
                    }

                    setError(Object.values(errors)[0] ?? 'Gagal menambahkan ke keranjang.');
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    const add = async (anchor) => {
        setError('');
        setProcessing(true);

        try {
            const response = await fetch(`/api/cabang/untuk-produk/${productId}`, {
                headers: { Accept: 'application/json' },
            });

            if (! response.ok) {
                throw new Error('gagal memuat');
            }

            const { branches } = await response.json();
            const branch = branches.find((candidate) => candidate.selectable);

            if (! branch) {
                setError('Stok sedang kosong di semua cabang.');
                setProcessing(false);

                return;
            }

            submit(branch.id, false, anchor);
        } catch {
            setError('Tidak bisa menambahkan. Coba lagi nanti.');
            setProcessing(false);
        }
    };

    return { add, processing, error };
}
