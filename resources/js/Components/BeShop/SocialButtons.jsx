import { router } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Social sign-in circles.
 *
 * Each provider signs the shopper in through the same prototype endpoint as the
 * email form, using a stand-in address for that provider.
 */
export default function SocialButtons() {
    const signInWith = (provider) =>
        router.post('/ui/signin', {
            email: `kristin.watson@${provider}.test`,
            password: 'social',
        });

    return (
        <div className="mb-5 flex justify-center gap-3">
            <button
                type="button"
                onClick={() => signInWith('facebook')}
                aria-label="Continue with Facebook"
                className="flex h-11 w-11 items-center justify-center rounded-full bg-[#3b5998] text-white"
            >
                <Icon name="facebook" size={18} />
            </button>

            <button
                type="button"
                onClick={() => signInWith('twitter')}
                aria-label="Continue with Twitter"
                className="flex h-11 w-11 items-center justify-center rounded-full bg-[#1da1f2] text-white"
            >
                <Icon name="twitter" size={18} />
            </button>

            <button
                type="button"
                onClick={() => signInWith('google')}
                aria-label="Continue with Google"
                className="flex h-11 w-11 items-center justify-center rounded-full border border-[#dddddd] bg-white"
            >
                <svg width="16" height="16" viewBox="0 0 18 18" aria-hidden="true">
                    <path
                        d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 01-1.8 2.72v2.26h2.91c1.7-1.57 2.68-3.88 2.68-6.62z"
                        fill="#4285F4"
                    />
                    <path
                        d="M9 18c2.43 0 4.47-.81 5.96-2.18l-2.91-2.26c-.81.54-1.84.86-3.05.86-2.34 0-4.33-1.58-5.04-3.71H.96v2.33A9 9 0 009 18z"
                        fill="#34A853"
                    />
                    <path
                        d="M3.96 10.71A5.41 5.41 0 013.68 9c0-.59.1-1.17.28-1.71V4.96H.96A9 9 0 000 9c0 1.45.35 2.83.96 4.04l3.01-2.33z"
                        fill="#FBBC05"
                    />
                    <path
                        d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 00.96 4.96L3.96 6.29C4.67 4.17 6.66 3.58 9 3.58z"
                        fill="#EA4335"
                    />
                </svg>
            </button>
        </div>
    );
}
