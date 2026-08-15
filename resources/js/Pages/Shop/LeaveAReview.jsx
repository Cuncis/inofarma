import { useState } from 'react';
import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/Shop/AppBar';
import Button from '@/Components/Shop/Button';
import Icon from '@/Components/Shop/Icon';
import { asset } from '@/Components/Shop/data';

export default function LeaveAReview() {
    const [score, setScore] = useState(4);
    const [body, setBody] = useState('');

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/reviews');
    };

    return (
        <MobileLayout
            title="Beri Ulasan"
            header={<AppBar title="Beri Ulasan" back="/ui/order-history" tone="white" />}
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-4 text-center">
                <img
                    src={asset.other('07')}
                    alt=""
                    className="mx-auto mb-3 h-[140px] w-[140px] object-contain"
                />

                <h2 className="mb-3.5 font-display text-lg leading-[1.35]">
                    Beri penilaian untuk kualitas
                    <br />
                    layanan pesanan Anda!
                </h2>

                <div className="mb-3.5 flex justify-center gap-1.5">
                    {[1, 2, 3, 4, 5].map((star) => (
                        <button
                            key={star}
                            type="button"
                            onClick={() => setScore(star)}
                            aria-label={`Beri nilai ${star} dari 5`}
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
                    Komentar dan saran Anda membantu kami meningkatkan kualitas layanan!
                </p>

                <textarea
                    value={body}
                    onChange={(event) => setBody(event.target.value)}
                    placeholder="Tulis ulasan Anda di sini..."
                    rows={3}
                    className="mb-3.5 w-full resize-none border border-line p-3 text-left text-xs text-muted placeholder:text-[#bbbbbb] focus:outline-none focus:ring-0"
                />

                <Button type="submit">Kirim Ulasan</Button>
            </form>
        </MobileLayout>
    );
}
