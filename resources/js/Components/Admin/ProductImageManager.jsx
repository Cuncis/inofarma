import { router } from '@inertiajs/react';
import { useState } from 'react';
import Badge from './Badge';
import Card from './Card';
import ConfirmDialog from './ConfirmDialog';
import { DropZone } from './Form';
import Icon from './Icon';

/**
 * Upload, reorder, primary-select and delete a product's photos.
 *
 * Only shown on the edit screen — a product needs to exist first, since every
 * image belongs to `{product}/gambar`. Reordering is two buttons rather than
 * drag-and-drop: with a handful of photos per product that is plenty, and it
 * needs no extra dependency.
 *
 * @param {{ product: { id: string, images: object[] } }} props
 */
export default function ProductImageManager({ product }) {
    const [pendingDelete, setPendingDelete] = useState(null);
    const [uploading, setUploading] = useState(false);
    const [busyId, setBusyId] = useState(null);

    const images = [...product.images].sort((a, b) => a.position - b.position);
    const base = `/admin/produk/${product.id}/gambar`;

    const upload = (files) => {
        setUploading(true);

        router.post(
            base,
            { images: files },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => setUploading(false),
            },
        );
    };

    const move = (image, direction) => {
        const index = images.findIndex((item) => item.id === image.id);
        const swapIndex = index + direction;

        if (swapIndex < 0 || swapIndex >= images.length) {
            return;
        }

        const order = [...images];
        [order[index], order[swapIndex]] = [order[swapIndex], order[index]];

        setBusyId(image.id);
        router.post(
            `${base}/urutkan`,
            { order: order.map((item) => item.id) },
            { preserveScroll: true, onFinish: () => setBusyId(null) },
        );
    };

    const makePrimary = (image) => {
        setBusyId(image.id);
        router.post(
            `${base}/${image.id}/utama`,
            {},
            { preserveScroll: true, onFinish: () => setBusyId(null) },
        );
    };

    const confirmDelete = () => {
        const target = pendingDelete;
        setBusyId(target.id);

        router.delete(`${base}/${target.id}`, {
            preserveScroll: true,
            onFinish: () => {
                setBusyId(null);
                setPendingDelete(null);
            },
        });
    };

    return (
        <Card title="Gambar Produk">
            <div className="space-y-4">
                {images.length ? (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                        {images.map((image, index) => (
                            <div
                                key={image.id}
                                className="overflow-hidden rounded-lg border border-admin-border dark:border-admin-dark-border"
                            >
                                <div className="relative">
                                    <img src={image.thumb} alt="" className="h-24 w-full object-cover" />
                                    {image.isPrimary ? (
                                        <span className="absolute left-1.5 top-1.5">
                                            <Badge tone="success">Utama</Badge>
                                        </span>
                                    ) : null}
                                </div>

                                <div className="flex items-center justify-between gap-1 bg-admin-hover px-1 py-1 dark:bg-admin-dark-hover">
                                    <div className="flex gap-0.5">
                                        <button
                                            type="button"
                                            disabled={index === 0 || busyId === image.id}
                                            onClick={() => move(image, -1)}
                                            title="Pindah ke depan"
                                            className="rounded p-1 text-admin-muted hover:text-admin-heading disabled:opacity-30 dark:text-admin-dark-muted dark:hover:text-admin-dark-heading"
                                        >
                                            <Icon name="solar:arrow-up-bold-duotone" size={15} />
                                        </button>
                                        <button
                                            type="button"
                                            disabled={index === images.length - 1 || busyId === image.id}
                                            onClick={() => move(image, 1)}
                                            title="Pindah ke belakang"
                                            className="rounded p-1 text-admin-muted hover:text-admin-heading disabled:opacity-30 dark:text-admin-dark-muted dark:hover:text-admin-dark-heading"
                                        >
                                            <Icon name="solar:arrow-down-bold-duotone" size={15} />
                                        </button>
                                    </div>

                                    <div className="flex gap-0.5">
                                        {! image.isPrimary ? (
                                            <button
                                                type="button"
                                                disabled={busyId === image.id}
                                                onClick={() => makePrimary(image)}
                                                title="Jadikan gambar utama"
                                                className="rounded p-1 text-admin-muted hover:text-brand disabled:opacity-30 dark:text-admin-dark-muted"
                                            >
                                                <Icon name="solar:star-bold" size={15} />
                                            </button>
                                        ) : null}
                                        <button
                                            type="button"
                                            disabled={busyId === image.id}
                                            onClick={() => setPendingDelete(image)}
                                            title="Hapus gambar"
                                            className="rounded p-1 text-admin-muted hover:text-danger disabled:opacity-30 dark:text-admin-dark-muted"
                                        >
                                            <Icon name="solar:trash-bin-minimalistic-bold-duotone" size={15} />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <p className="text-xs text-admin-muted dark:text-admin-dark-muted">
                        Belum ada gambar. Kartu produk memakai gambar bawaan sampai satu diunggah.
                    </p>
                )}

                <DropZone
                    hint={
                        uploading
                            ? 'Mengunggah…'
                            : 'PNG, JPG atau WEBP maksimal 5 MB, bisa pilih beberapa sekaligus'
                    }
                    multiple
                    accept="image/png,image/jpeg,image/webp"
                    onFiles={upload}
                />
            </div>

            <ConfirmDialog
                open={Boolean(pendingDelete)}
                title="Hapus gambar?"
                body={`Gambar ini akan dihapus dan tidak bisa dikembalikan.`}
                confirmLabel="Hapus"
                processing={busyId === pendingDelete?.id}
                onConfirm={confirmDelete}
                onCancel={() => setPendingDelete(null)}
            />
        </Card>
    );
}
