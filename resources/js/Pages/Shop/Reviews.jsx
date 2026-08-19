import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import ReviewCard from '@/Components/Shop/ReviewCard';
import { reviews } from '@/Components/Shop/data';

export default function Reviews() {
    return (
        <MobileLayout
            title="Ulasan"
            header={<AppBar title="Ulasan" back="/ui/product-detail" tone="brand" />}
        >
            <div className="flex-1 overflow-y-auto p-3.5">
                {reviews.map((review) => (
                    <ReviewCard key={review.author} review={review} />
                ))}
            </div>
        </MobileLayout>
    );
}
