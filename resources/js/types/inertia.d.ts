import '@inertiajs/core';

declare module '@inertiajs/core' {
    interface InertiaConfig {
        sharedPageProps: {
            auth: { user: { id: number; name: string; email: string } | null };
            shopUser: ShopUser | null;
            flash: { success: string | null; error: string | null };
            catalog?: Catalog;
            cartCount?: number;
        };
    }
}
