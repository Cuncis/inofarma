import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';
import { asset } from '@/Components/BeShop/data';

export default function PromocodesEmpty() {
    return (
        <MobileLayout
            title="Promocodes Empty"
            header={<AppBar title="Add a Promocode" back="/ui/profile" tone="white" />}
        >
            <div className="flex flex-1 flex-col items-center justify-center overflow-y-auto p-6 text-center">
                <img
                    src={asset.other('06')}
                    alt=""
                    className="mx-auto mb-3.5 h-[180px] w-[180px] object-contain"
                />

                <h2 className="mb-2.5 font-display text-[21px] leading-tight">
                    You do not have
                    <br />
                    promocodes yet!
                </h2>

                <p className="mb-[18px] text-xs leading-relaxed text-muted">
                    Qui ex aute ipsum duis. Incididunt
                    <br />
                    adipisicing voluptate laborum
                </p>

                <div className="mb-3 w-full">
                    <Field value="Discount2022" />
                </div>

                <Button>Add a Promocode</Button>
            </div>
        </MobileLayout>
    );
}
