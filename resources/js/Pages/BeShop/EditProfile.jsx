import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Field from '@/Components/BeShop/Field';
import { asset } from '@/Components/BeShop/data';

export default function EditProfile() {
    return (
        <MobileLayout
            title="Edit Profile"
            header={<AppBar title="Edit Profile" back="/ui/profile" tone="white" />}
        >
            <div className="flex flex-1 flex-col items-center overflow-y-auto bg-blush px-[22px] py-[18px]">
                <div className="mx-auto mb-[18px] mt-3.5 h-[108px] w-[108px] overflow-hidden rounded-full border-[5px] border-brand">
                    <img
                        src={asset.user('01')}
                        alt="Kristin Watson"
                        className="h-full w-full object-cover"
                    />
                </div>

                <div className="w-full">
                    <Field value="kristinwatson@mail.com" className="mb-2.5" />
                    <Field value="••••••••" icon="eyeOff" className="mb-2.5" />
                    <Field value="+17 123456789" className="mb-2.5" />
                    <Field value="Enter your address" className="mb-2.5" />

                    <Button>Save Changes</Button>
                </div>
            </div>
        </MobileLayout>
    );
}
