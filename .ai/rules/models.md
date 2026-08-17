---
paths:
  - 'app/Support/ProductImageUploader.php,app/Http/Controllers/Admin/ProductImageController.php,app/Models/ProductImage.php'
---

# Models

## Product image uploads: disk config, not env, and convention-based thumbnails
Uploads always go through `Storage::disk(config('filesystems.uploads'))` (`ProductImageUploader`), never a hardcoded 'public'. That config key defaults to 'public' but is swappable to 's3' via `UPLOADS_DISK` + `AWS_*` env vars with zero code changes — that's the whole "S3-compatible" story from Fase 4.1.

Each upload makes two files via intervention/image v4 (`ImageManager::usingDriver(GdDriver::class)`): the original (scaled down to 1600px, JPEG q82) and a 400x400 thumb. Only `path` (the original) is stored on `product_images`. The thumb's path is never a column — `ProductImage::getThumbPathAttribute()` derives it by string convention (`-thumb` before the extension via `ProductImageUploader::thumbPath()`). Don't add a `thumb_path` column; extend the convention instead.

Seeded/demo images use static paths under `/media/...` (not the uploads disk) — `ProductImageController::destroy()` and tests must check `str_starts_with($path, '/media/')` before calling `ProductImageUploader::destroy()`, or it'll try to delete a file that was never on that disk.
