/**
 * The 37 BeShop screens in presentation order.
 *
 * This is the single source of truth for the screen index page, the prev/next
 * pager in the layout, and the route table in `routes/web.php`.
 *
 * @type {{ group: string, screens: { number: number, name: string, slug: string }[] }[]}
 */
export const screenGroups = [
    {
        group: 'Auth',
        screens: [
            { number: 1, name: 'Sign In', slug: 'signin' },
            { number: 2, name: 'Sign Up', slug: 'signup' },
            { number: 3, name: 'Forgot Password', slug: 'forgot-password' },
            { number: 4, name: 'Verify Phone', slug: 'verify-phone' },
            { number: 5, name: 'OTP Code', slug: 'otp-code' },
        ],
    },
    {
        group: 'Onboarding',
        screens: [
            { number: 6, name: 'Account Created', slug: 'account-created' },
            { number: 7, name: 'Email Sent', slug: 'email-sent' },
            { number: 8, name: 'New Password', slug: 'new-password' },
        ],
    },
    {
        group: 'Main Tabs',
        screens: [
            { number: 9, name: 'Home', slug: 'home' },
            { number: 10, name: 'Cart / Order', slug: 'cart' },
            { number: 11, name: 'Wishlist', slug: 'wishlist' },
            { number: 12, name: 'Profile', slug: 'profile' },
        ],
    },
    {
        group: 'Shop & Product',
        screens: [
            { number: 13, name: 'Categories', slug: 'categories' },
            { number: 14, name: 'Shop', slug: 'shop' },
            { number: 15, name: 'Product Detail', slug: 'product-detail' },
            { number: 16, name: 'Filter', slug: 'filter' },
        ],
    },
    {
        group: 'Checkout Flow',
        screens: [
            { number: 17, name: 'Checkout', slug: 'checkout' },
            { number: 18, name: 'Shipping Details', slug: 'shipping-details' },
            { number: 19, name: 'Payment Method', slug: 'payment-method' },
            { number: 20, name: 'Order Successful', slug: 'order-successful' },
            { number: 21, name: 'Order Failed', slug: 'order-failed' },
        ],
    },
    {
        group: 'Empty States',
        screens: [
            { number: 22, name: 'Cart Empty', slug: 'cart-empty' },
            { number: 23, name: 'Wishlist Empty', slug: 'wishlist-empty' },
            { number: 24, name: 'Promocodes Empty', slug: 'promocodes-empty' },
            { number: 25, name: 'Order History Empty', slug: 'order-history-empty' },
        ],
    },
    {
        group: 'Profile Screens',
        screens: [
            { number: 26, name: 'Edit Profile', slug: 'edit-profile' },
            { number: 27, name: 'Payment Methods', slug: 'payment-methods' },
            { number: 28, name: 'Add New Card', slug: 'add-new-card' },
            { number: 29, name: 'My Address', slug: 'my-address' },
            { number: 30, name: 'Add New Address', slug: 'add-new-address' },
            { number: 31, name: 'My Promocodes', slug: 'my-promocodes' },
            { number: 32, name: 'Order History', slug: 'order-history' },
        ],
    },
    {
        group: 'Info & Reviews',
        screens: [
            { number: 33, name: 'Track Your Order', slug: 'track-order' },
            { number: 34, name: 'Shipping Info', slug: 'shipping-info' },
            { number: 35, name: 'FAQ', slug: 'faq' },
            { number: 36, name: 'Reviews', slug: 'reviews' },
            { number: 37, name: 'Leave a Review', slug: 'leave-a-review' },
        ],
    },
];

/** Flat list of all 37 screens, ordered by screen number. */
export const allScreens = screenGroups.flatMap((group) =>
    group.screens.map((screen) => ({ ...screen, group: group.group })),
);

/**
 * Resolve the screens that sit either side of the given slug, for the pager.
 *
 * @param {string} slug
 * @returns {{ previous: object|null, next: object|null, current: object|null }}
 */
export function findNeighbours(slug) {
    const index = allScreens.findIndex((screen) => screen.slug === slug);

    if (index === -1) {
        return { previous: null, next: null, current: null };
    }

    return {
        previous: allScreens[index - 1] ?? null,
        current: allScreens[index],
        next: allScreens[index + 1] ?? null,
    };
}
