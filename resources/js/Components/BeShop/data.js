const assets = 'https://george-fx.github.io/beshop-data/assets';

export const asset = {
    product: (n) => `${assets}/products/${n}.jpg`,
    banner: (n) => `${assets}/banners/${n}.jpg`,
    category: (n) => `${assets}/categories/${n}.jpg`,
    user: (n) => `${assets}/users/${n}.jpg`,
    other: (n) => `${assets}/other/${n}.png`,
};

/** @type {import('./ProductCard').Product[]} */
export const trendingProducts = [
    { name: 'Silk Maxi Dress', image: asset.product('01'), price: '$89.99', rating: '4.9' },
    { name: 'Floral Blouse', image: asset.product('02'), price: '$42.00', rating: '4.7' },
    { name: 'Leather Jacket', image: asset.product('03'), price: '$145.00', rating: '4.5' },
    { name: 'Summer Dress', image: asset.product('04'), price: '$38.00', rating: '4.8' },
];

/** @type {import('./ProductCard').Product[]} */
export const newArrivals = [
    {
        name: 'Denim Skirt',
        image: asset.product('05'),
        price: '$36.00',
        oldPrice: '$52',
        rating: '4.6',
    },
    { name: 'Casual Tee', image: asset.product('06'), price: '$24.99', rating: '4.3' },
    {
        name: 'Wide-leg Pants',
        image: asset.product('07'),
        price: '$55.00',
        oldPrice: '$78',
        rating: '4.6',
    },
    { name: 'Linen Blazer', image: asset.product('08'), price: '$98.00', rating: '4.8' },
];

/** @type {import('./ProductCard').Product[]} */
export const shopProducts = [
    {
        name: 'Silk Maxi Dress',
        image: asset.product('01'),
        price: '$89.99',
        oldPrice: '$99',
        rating: '4.9',
    },
    {
        name: 'Floral Blouse',
        image: asset.product('02'),
        price: '$42.00',
        oldPrice: '$59',
        rating: '4.7',
    },
    { name: 'Leather Jacket', image: asset.product('03'), price: '$145.00', rating: '4.5' },
    { name: 'Summer Dress', image: asset.product('04'), price: '$38.00', rating: '4.8' },
    { name: 'Casual Tee', image: asset.product('06'), price: '$24.99', rating: '4.3' },
    {
        name: 'Wide-leg Pants',
        image: asset.product('07'),
        price: '$55.00',
        oldPrice: '$78',
        rating: '4.6',
    },
];

/** @type {import('./ProductCard').Product[]} */
export const wishlistProducts = [
    {
        name: 'Silk Maxi Dress',
        image: asset.product('01'),
        price: '$89.99',
        oldPrice: '$99',
        rating: '4.9',
    },
    {
        name: 'Floral Blouse',
        image: asset.product('02'),
        price: '$42.00',
        oldPrice: '$59',
        rating: '4.7',
    },
    { name: 'Summer Dress', image: asset.product('04'), price: '$38.00', rating: '4.8' },
    {
        name: 'Denim Skirt',
        image: asset.product('05'),
        price: '$36.00',
        oldPrice: '$52',
        rating: '4.6',
    },
];

export const categories = [
    { name: 'Women', image: asset.category('01') },
    { name: 'Men', image: asset.category('02') },
    { name: 'Kids', image: asset.category('03') },
    { name: 'Accessories', image: asset.category('04') },
    { name: 'Home', image: asset.category('05') },
    { name: 'Beauty', image: asset.category('06') },
];

export const cartItems = [
    {
        name: 'Silk Maxi Dress',
        image: asset.product('01'),
        price: '$89.99',
        quantity: 2,
        onSale: false,
    },
    {
        name: 'Leather Jacket',
        image: asset.product('03'),
        price: '$145.00',
        quantity: 1,
        onSale: true,
    },
];

/** @type {import('./ReviewCard').Review[]} */
export const reviews = [
    {
        author: 'Sarah J.',
        avatar: asset.user('02'),
        score: 5,
        age: '2d ago',
        body: 'Absolutely love this dress! The fabric is so silky and the fit is perfect.',
    },
    {
        author: 'Mike R.',
        avatar: asset.user('03'),
        score: 4,
        age: '5d ago',
        body: 'Great quality but runs a bit small. Order one size up for the best fit.',
    },
    {
        author: 'Emma L.',
        avatar: asset.user('04'),
        score: 5,
        age: '1w ago',
        body: 'Fast shipping and beautiful packaging. Will definitely buy again!',
    },
    {
        author: 'Tom H.',
        avatar: asset.user('05'),
        score: 3,
        age: '2w ago',
        body: 'Decent product for the price. The color is slightly different from the photo.',
    },
];

export const addresses = [
    { title: 'Home', line: '8000 S Kirkland Ave, Chicago, IL 60652' },
    { title: 'Office', line: '1234 N Michigan Ave, Chicago, IL 60601' },
    { title: "Parent's Home", line: '5678 W Addison St, Chicago, IL 60641' },
];

export const cards = ['**** **** **** 6644', '**** **** **** 8821'];

export const promocodes = [
    {
        name: 'Summer Sale',
        discount: '15% off',
        tone: 'text-success',
        expires: 'Expires: Dec 31, 2025',
        code: 'SUMMER15',
    },
    {
        name: 'Flash Deal',
        discount: '25% off',
        tone: 'text-warning',
        expires: 'Expires: Aug 31, 2025',
        code: 'FLASH25',
    },
    {
        name: 'VIP Member',
        discount: '40% off',
        tone: 'text-brand',
        expires: 'Expires: Jan 01, 2026',
        code: 'VIP40',
    },
];

export const faqs = [
    {
        question: 'Can I place an order online?',
        answer: 'Yes, you can easily place your order through our website or mobile app.',
        open: true,
    },
    { question: 'Do you offer skincare products?', open: false },
    { question: 'Do you offer gift wrapping?', open: false },
    { question: 'Are your products cruelty-free?', open: false },
    { question: 'How can I track my order?', open: false },
];

export const swatches = [
    '#222222',
    '#c4a882',
    '#d05278',
    '#7ecac5',
    '#4a90e2',
    '#f5a623',
    '#7ed321',
    '#bd10e0',
];

export const sizes = ['XS', 'S', 'M', 'L', 'XL'];
