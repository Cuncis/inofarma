import { useState } from 'react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Checkbox from '@/Components/BeShop/Checkbox';
import { swatches } from '@/Components/BeShop/data';

const tags = ['Atasan', 'Bawahan', 'Dress', 'Outerwear', 'Aksesori', 'Sepatu'];

export default function Filter() {
    const [color, setColor] = useState(swatches[0]);
    const [labels, setLabels] = useState({ sale: true, new: false });
    const [selectedTags, setSelectedTags] = useState(['Atasan']);

    const toggleTag = (tag) =>
        setSelectedTags((current) =>
            current.includes(tag)
                ? current.filter((existing) => existing !== tag)
                : [...current, tag],
        );

    return (
        <MobileLayout
            title="Filter"
            header={<AppBar title="Filter" back="/ui/shop" />}
            footer={
                <div className="border-t border-line p-3.5">
                    <Button href="/ui/shop">Terapkan Filter</Button>
                </div>
            }
        >
            <div className="flex-1 overflow-y-auto p-4">
                <div className="mb-2.5 font-display text-[15px]">Warna</div>

                <div className="mb-[18px] flex flex-wrap gap-2.5">
                    {swatches.map((swatch) => (
                        <button
                            key={swatch}
                            type="button"
                            onClick={() => setColor(swatch)}
                            aria-label={`Saring berdasarkan warna ${swatch}`}
                            style={{ background: swatch }}
                            className={`h-[29px] w-[29px] rounded-full ${
                                color === swatch
                                    ? 'outline outline-2 outline-offset-2 outline-brand'
                                    : ''
                            }`}
                        />
                    ))}
                </div>

                <div className="mb-2.5 font-display text-[15px]">Label</div>

                <div className="mb-2.5">
                    <Checkbox
                        checked={labels.sale}
                        onChange={() =>
                            setLabels((current) => ({ ...current, sale: ! current.sale }))
                        }
                        size={17}
                        label={
                            <span className="bg-sale px-3 py-1 text-[11px] font-bold text-ink">
                                DISKON
                            </span>
                        }
                    />
                </div>

                <div className="mb-[18px]">
                    <Checkbox
                        checked={labels.new}
                        onChange={() =>
                            setLabels((current) => ({ ...current, new: ! current.new }))
                        }
                        size={17}
                        label={
                            <span className="bg-brand px-3 py-1 text-[11px] font-bold text-white">
                                BARU
                            </span>
                        }
                    />
                </div>

                <div className="mb-2.5 font-display text-[15px]">Kategori</div>

                <div className="mb-5 flex flex-wrap gap-2">
                    {tags.map((tag) => (
                        <button
                            key={tag}
                            type="button"
                            onClick={() => toggleTag(tag)}
                            className={`px-3.5 py-[7px] text-xs ${
                                selectedTags.includes(tag)
                                    ? 'border border-brand text-brand'
                                    : 'border border-line text-muted'
                            }`}
                        >
                            {tag}
                        </button>
                    ))}
                </div>
            </div>
        </MobileLayout>
    );
}
