/**
 * Image paths.
 *
 * Everything lives under `public/media` because the storefront and the admin
 * show the same pictures. Product and category images now come back from the
 * server with the record they belong to; these helpers are for the fixtures
 * that are still local to the front end (avatars, brand marks).
 */
export const media = {
    product: (n) => `/media/images/product/p-${n}.png`,
    category: (n) => `/media/images/small/img-${n}.jpg`,
    user: (n) => `/media/images/users/avatar-${n}.jpg`,
    seller: (n) => `/media/images/seller/${n}.svg`,
    brand: (n) => `/media/images/brands/${n}.png`,
};
