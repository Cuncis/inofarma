import { useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import MobileLayout from '@/Layouts/MobileLayout';
import AppBar from '@/Components/BeShop/AppBar';
import Button from '@/Components/BeShop/Button';

export default function OtpCode() {
    const [digits, setDigits] = useState(['4', '2', '7', '1', '']);
    const inputs = useRef([]);

    const updateDigit = (index, value) => {
        const digit = value.replace(/\D/g, '').slice(-1);

        setDigits((current) => current.map((existing, at) => (at === index ? digit : existing)));

        if (digit && index < digits.length - 1) {
            inputs.current[index + 1]?.focus();
        }
    };

    const handleKeyDown = (index, event) => {
        if (event.key === 'Backspace' && ! digits[index] && index > 0) {
            inputs.current[index - 1]?.focus();
        }
    };

    const submit = (event) => {
        event.preventDefault();

        router.visit('/ui/account-created');
    };

    return (
        <MobileLayout
            title="Confirmation Code"
            header={
                <AppBar title="Verify Phone Number" back="/ui/verify-phone" tone="white" />
            }
        >
            <form onSubmit={submit} className="flex-1 overflow-y-auto p-5">
                <div className="bg-blush p-6">
                    <p className="mb-[18px] text-[13px] leading-[1.7] text-muted">
                        Enter your OTP code here.
                    </p>

                    <div className="mb-4 flex gap-2">
                        {digits.map((digit, index) => (
                            <input
                                key={index}
                                ref={(element) => (inputs.current[index] = element)}
                                value={digit}
                                onChange={(event) => updateDigit(index, event.target.value)}
                                onKeyDown={(event) => handleKeyDown(index, event)}
                                inputMode="numeric"
                                maxLength={1}
                                aria-label={`Digit ${index + 1}`}
                                className={`aspect-square min-w-0 flex-1 bg-transparent p-0 text-center text-xl font-bold focus:outline-none focus:ring-0 ${
                                    digit
                                        ? 'border-2 border-ink'
                                        : 'border border-[#dddddd] focus:border-ink'
                                }`}
                            />
                        ))}
                    </div>

                    <div className="mb-4 flex gap-1 text-xs">
                        <span>Did not receive the OTP?</span>
                        <button
                            type="button"
                            onClick={() => setDigits(['', '', '', '', ''])}
                            className="text-brand"
                        >
                            Resend.
                        </button>
                    </div>

                    <Button type="submit">Verify</Button>
                </div>
            </form>
        </MobileLayout>
    );
}
