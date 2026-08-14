import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import { asset } from '@/Components/BeShop/data';

export default function LeaveAReview() {
    return (
        <MobileLayout
            title="Leave a Review"
            header={<AppBar title="Leave a Review" back="/ui/order-history" tone="white" />}
        >
            <div className="flex-1 overflow-y-auto p-4 text-center">
                <img
                    src={asset.other('07')}
                    alt=""
                    className="mx-auto mb-3 h-[140px] w-[140px] object-contain"
                />

                <h2 className="mb-3.5 font-display text-lg leading-[1.35]">
                    Please rate the quality of
                    <br />
                    service for the order!
                </h2>

                <div className="mb-3.5 flex justify-center gap-1.5">
                    {[1, 2, 3, 4, 5].map((star) => (
                        <Icon
                            key={star}
                            name="star"
                            size={28}
                            className={star <= 4 ? 'text-star' : 'text-[#dddddd]'}
                        />
                    ))}
                </div>

                <p className="mb-3.5 text-xs leading-relaxed text-muted">
                    Your comments and suggestions help us improve the service quality!
                </p>

                <div className="mb-3.5 min-h-[80px] border border-line p-3 text-left text-xs text-[#bbbbbb]">
                    Write your review here...
                </div>

                <Button>Submit</Button>
            </div>
        </MobileLayout>
    );
}
