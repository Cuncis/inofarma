<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

/**
 * Resizes an uploaded product photo into two JPEG variants and stores both on
 * the `filesystems.uploads` disk (the local `public` disk in development).
 *
 * Swapping to real object storage in production is a config change, not a
 * code change: set `UPLOADS_DISK=s3` plus the `AWS_*` keys in `.env` and every
 * upload lands on the already-scaffolded `s3` disk instead.
 *
 * The thumbnail's path is never stored — `ProductImage::getThumbPathAttribute()`
 * derives it from `path` by convention (`-thumb` before the extension), so
 * adding a variant never means adding a column.
 */
class ProductImageUploader
{
    private const MAX_DIMENSION = 1600;

    private const THUMB_DIMENSION = 400;

    private const QUALITY = 82;

    public const THUMB_SUFFIX = '-thumb';

    /**
     * @return array{path: string, disk: string}
     */
    public static function store(UploadedFile $file, int $productId): array
    {
        $manager = ImageManager::usingDriver(GdDriver::class);
        $source = $manager->decodePath($file->getRealPath());

        $name = (string) Str::uuid();
        $directory = "products/{$productId}";
        $path = "{$directory}/{$name}.jpg";
        $thumbPath = self::thumbPath($path);

        $original = clone $source;
        $original->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);

        $thumb = clone $source;
        $thumb->cover(self::THUMB_DIMENSION, self::THUMB_DIMENSION);

        $disk = self::disk();
        $disk->put($path, (string) $original->encodeUsingFormat(Format::JPEG, quality: self::QUALITY));
        $disk->put($thumbPath, (string) $thumb->encodeUsingFormat(Format::JPEG, quality: self::QUALITY));

        return ['path' => $disk->url($path), 'disk' => config('filesystems.uploads')];
    }

    /** Deletes both the original and its derived thumbnail; missing files are not an error. */
    public static function destroy(string $url): void
    {
        $disk = self::disk();
        $path = self::pathFromUrl($disk, $url);

        $disk->delete([$path, self::thumbPath($path)]);
    }

    public static function thumbPath(string $path): string
    {
        return preg_replace('/(\.\w+)$/', self::THUMB_SUFFIX.'$1', $path) ?? $path;
    }

    private static function disk(): FilesystemAdapter
    {
        return Storage::disk(config('filesystems.uploads'));
    }

    private static function pathFromUrl(FilesystemAdapter $disk, string $url): string
    {
        $prefix = rtrim($disk->url(''), '/').'/';

        return Str::startsWith($url, $prefix) ? Str::after($url, $prefix) : $url;
    }
}
