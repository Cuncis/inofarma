import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import ReviewCard from '@/Components/BeShop/ReviewCard';
import { reviews } from '@/Components/BeShop/data';

export default function Reviews() {
    return (
        <MobileLayout
            title="Reviews"
            header={<AppBar title="Reviews" back="/ui/product-detail" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                {reviews.map((review) => (
                    <ReviewCard key={review.author} review={review} />
                ))}
            </div>
        </MobileLayout>
    );
}
