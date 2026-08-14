import { useState } from 'react';
import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';
import Icon from '@/Components/BeShop/Icon';
import { asset } from '@/Components/BeShop/data';

export default function LeaveAReview() {
    const [score, setScore] = useState(4);
    const [body, setBody] = useState('');

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/reviews');
    };

    return (
        <MobileLayout
            title="Leave a Review"
            header={<AppBar title="Leave a Review" back="/ui/order-history" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-4 text-center">
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
                        <button
                            key={star}
                            type="button"
                            onClick={() => setScore(star)}
                            aria-label={`Rate ${star} out of 5`}
                        >
                            <Icon
                                name="star"
                                size={28}
                                className={star <= score ? 'text-star' : 'text-[#dddddd]'}
                            />
                        </button>
                    ))}
                </div>

                <p className="mb-3.5 text-xs leading-relaxed text-muted">
                    Your comments and suggestions help us improve the service quality!
                </p>

                <textarea
                    value={body}
                    onChange={(event) => setBody(event.target.value)}
                    placeholder="Write your review here..."
                    rows={3}
                    className="mb-3.5 w-full resize-none border border-line p-3 text-left text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-none focus:ring-0"
                />

                <Button type="submit">Submit</Button>
            </form>
        </MobileLayout>
    );
}
