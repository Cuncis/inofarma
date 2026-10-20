# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.

| Applies to | Rule file |
| --- | --- |
| app/Support/Shipping/**,app/Support/Pickup/**,app/Http/Controllers/Webhooks/**,app/Http/Controllers/Shop/CheckoutController.php,app/Http/Controllers/Shop/ShippingController.php,app/Filament/Resources/Orders/**,app/Filament/Resources/Pickups/**,app/Models/Shipment.php | .ai/rules/admin-models.md |
| app/Http/Controllers/Api/**,app/Support/LocationPreference.php,routes/api.php | .ai/rules/api-support.md |
| app/Models/Order.php, app/Models/Product.php | .ai/rules/app-models.md |
| app/Http/Controllers/Shop/BranchController.php,resources/js/Pages/Shop/BranchDetail.tsx,resources/js/Components/Shop/NearestPharmacies.tsx,app/Support/Presenters/StorefrontBranchPresenter.php,app/Filament/Resources/Branches/** | .ai/rules/branches.md |
| app/Support/Cart/** | .ai/rules/cart.md |
| app/Support/Payments/**,app/Http/Controllers/Webhooks/**,app/Http/Controllers/Shop/CheckoutController.php,app/Http/Controllers/Shop/PaymentController.php,app/Models/Payment.php,app/Console/Commands/ExpireUnpaidOrders.php,app/Support/OrderCancellation.php | .ai/rules/commands-support.md |
| app/Support/HomePageContent.php,app/Filament/Pages/EditHomePage.php,app/Filament/Pages/WebsiteContent.php,resources/js/Pages/Shop/Home.tsx,resources/js/Components/Shop/DesktopHome.tsx,app/Support/ContentStore.php,app/Support/FaqContent.php,app/Support/DocumentPage*.php,app/Support/SiteFooterContent.php,app/Filament/Pages/ContentEditorPage.php,app/Filament/Pages/Edit*Page.php,resources/js/Pages/Shop/Faq.tsx,resources/js/Pages/Shop/ShippingInfo.tsx,resources/js/Pages/Shop/RefundPolicy.tsx,resources/js/Components/Shop/LegalModal.tsx,resources/js/Layouts/DesktopLayout.tsx | .ai/rules/components-shop.md |
| app/Support/Auth/**,app/Http/Controllers/Admin/**,app/Filament/Resources/BranchStocks/**,app/Filament/Resources/StockTransfers/** | .ai/rules/controllers-admin.md |
| resources/js/Pages/Shop/OrderDetail.tsx,resources/js/Pages/Shop/TrackOrder.tsx,app/Http/Controllers/Shop/OrderController.php | .ai/rules/controllers-shop.md |
| app/Http/Controllers/** | .ai/rules/controllers.md |
| app/Support/CsvTemplates/** | .ai/rules/csv-templates.md |
| app/Models/Region.php,app/Console/Commands/ImportRegions.php,app/Http/Controllers/Shop/RegionController.php,database/data/** | .ai/rules/data.md |
| tests/Feature/** | .ai/rules/feature.md |
| resources/views/filament/** | .ai/rules/filament.md |
| resources/css/app.css | .ai/rules/general.md |
| app/Filament/Resources/Invoices/**,app/Models/Coupon.php,app/Filament/Resources/Coupons/** | .ai/rules/http-controllers-admin.md |
| app/Http/Controllers/Shop/CheckoutController.php | .ai/rules/http-controllers-shop.md |
| app/Models/*.php,app/Http/Middleware/Ensure*IsAuthenticated.php,config/auth.php | .ai/rules/http-middleware.md |
| app/Support/Inventory/** | .ai/rules/inventory.md |
| resources/js/lib/catalog.ts, resources/js/lib/media.ts, resources/js/Components/Shop/data.ts, resources/js/Components/Shop/ProductFulfilment.tsx | .ai/rules/lib.md |
| app/Http/Middleware/EnsureAdminIsAuthenticated.php | .ai/rules/middleware.md |
| database/migrations/** | .ai/rules/migrations.md |
| app/Support/Cart/**,app/Http/Controllers/Shop/CartController.php,app/Http/Controllers/Shop/CheckoutController.php,app/Models/Cart.php,app/Models/CartItem.php | .ai/rules/models-models.md |
| app/Support/ProductImageUploader.php,app/Filament/Resources/Products/RelationManagers/ImagesRelationManager.php,app/Models/ProductImage.php | .ai/rules/models.md |
| app/Filament/Resources/Newsletters/** | .ai/rules/newsletters.md |
| app/Observers/ChatMessageObserver.php | .ai/rules/observers.md |
| app/Http/Controllers/Shop/CheckoutController.php,app/Support/Cart/**,resources/js/Pages/Shop/Checkout.tsx,resources/js/Pages/Shop/Cart.tsx | .ai/rules/pages-shop.md |
| app/Support/Payments/** | .ai/rules/payments.md |
| app/Observers/**,app/Notifications/**,app/Support/Notifications/**,app/Support/Money.php,app/Console/Commands/NotifyExpiringBatches.php,app/Console/Commands/NotifyApproachingPickupDeadlines.php,app/Providers/Filament/AdminPanelProvider.php | .ai/rules/presenters.md |
| public/** | .ai/rules/public.md |
| app/Support/Shipping/**,app/Filament/Resources/Orders/** | .ai/rules/shipping-http-controllers-admin.md |
| app/Support/Shipping/** | .ai/rules/shipping.md |
| app/Http/Controllers/Shop/PrivacyController.php,app/Http/Controllers/Shop/AuthController.php,app/Support/Money.php,resources/js/Components/Shop/useLocationConsent.ts,resources/js/Components/Shop/legalDocuments.ts,resources/js/Components/Shop/LegalModal.tsx,resources/js/Pages/Shop/RefundPolicy.tsx,resources/js/Pages/Shop/ShippingInfo.tsx,app/Filament/Resources/Products/** | .ai/rules/shop-http-controllers-admin.md |
| resources/js/{Pages,Components}/Shop/** | .ai/rules/shop.md |
| app/Support/ProductCsvImporter.php,app/Filament/Pages/ProductImport.php | .ai/rules/support-http-controllers-admin.md |
| app/Support/**, app/Http/Controllers/Admin/**, app/Filament/** | .ai/rules/support.md |
