---
paths:
  - 'app/Support/ProductImageUploader.php,app/Filament/Resources/Products/RelationManagers/ImagesRelationManager.php,app/Models/ProductImage.php'
---

# Models

## Product image uploads: disk config, not env, and convention-based thumbnails
Uploads always go through `Storage::disk(config('filesystems.uploads'))` (`ProductImageUploader`), never a hardcoded 'public'. That config key defaults to 'public' but is swappable to 's3' via `UPLOADS_DISK` + `AWS_*` env vars with zero code changes — that's the whole "S3-compatible" story from Fase 4.1.

Each upload makes two files via intervention/image v4 (`ImageManager::usingDriver(GdDriver::class)`): the original (scaled down to 1600px, JPEG q82) and a 400x400 thumb. Only `path` (the original) is stored on `product_images`. The thumb's path is never a column — `ProductImage::getThumbPathAttribute()` derives it by string convention (`-thumb` before the extension via `ProductImageUploader::thumbPath()`). Don't add a `thumb_path` column; extend the convention instead.

Seeded/demo images use static paths under `/media/...` (not the uploads disk) — `ImagesRelationManager`'s delete action and tests must check `str_starts_with($path, '/media/')` before calling `ProductImageUploader::destroy()`, or it'll try to delete a file that was never on that disk.

`ImagesRelationManager` (shown as a tab on the product's edit page, matching the legacy screen's own "images require an existing product" design) intercepts Filament's `FileUpload` via `saveUploadedFileUsing()` to route the actual bytes through `ProductImageUploader::store()` instead of Filament's default storage — that's the only way to still get the resize/thumbnail pipeline. Reordering uses Filament's built-in `->reorderable('position')` rather than a bespoke drag handler.
