<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Every image the site can show, in one list for the admin's "Media File":
 * files staff uploaded there, product photos, and the storefront's own
 * artwork under `public/media`. Each item carries the full URL to copy.
 *
 * Only files uploaded through Media File (the `media/` folder of the uploads
 * disk) can be deleted from there. Product photos belong to their product and
 * the storefront artwork ships with the code, so both are read-only here.
 */
class MediaLibrary
{
    public const SOURCE_UPLOADED = 'unggahan';

    public const SOURCE_PRODUCT = 'produk';

    public const SOURCE_SITE = 'situs';

    public const UPLOAD_DIRECTORY = 'media';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'avif', 'pdf'];

    /** The suffix is bumped when the cached shape changes, so a stale entry is never read as the new one. */
    private const CACHE_KEY = 'media-library.items.v2';

    /**
     * @return Collection<int, array{source: string, path: string, name: string, folder: string, size: int, modified: int, url: string, isImage: bool, deletable: bool}>
     */
    public static function items(): Collection
    {
        // Cached as a plain array: the cache only unserializes scalars and arrays,
        // so a cached Collection would come back as an incomplete class.
        $cached = Cache::remember(self::CACHE_KEY, 60, fn () => self::scan()->all());

        if (! is_array($cached) || ($cached !== [] && ! is_array(reset($cached)))) {
            self::forget();
            $cached = self::scan()->all();
            Cache::put(self::CACHE_KEY, $cached, 60);
        }

        return collect($cached);
    }

    /**
     * @return Collection<int, array{source: string, path: string, name: string, folder: string, size: int, modified: int, url: string, isImage: bool, deletable: bool}>
     */
    public static function search(?string $source = null, ?string $term = null): Collection
    {
        $term = Str::lower(trim((string) $term));

        return self::items()
            ->when($source, fn (Collection $items) => $items->where('source', $source))
            ->when($term !== '', fn (Collection $items) => $items->filter(fn (array $item) => Str::contains(Str::lower($item['path']), $term)))
            ->values();
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(config('filesystems.uploads'));
    }

    /**
     * Removes a file uploaded through Media File. Anything else is refused.
     */
    public static function delete(string $path): bool
    {
        if (! self::isUploadedHere($path)) {
            return false;
        }

        self::disk()->delete($path);
        self::forget();

        return true;
    }

    public static function isUploadedHere(string $path): bool
    {
        return str_starts_with($path, self::UPLOAD_DIRECTORY.'/') && ! str_contains($path, '..');
    }

    /**
     * A readable, collision-proof file name: "banner-promo-x7k2p.png".
     */
    public static function storedName(string $originalName): string
    {
        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'file';

        return $base.'-'.Str::lower(Str::random(5)).'.'.Str::lower(pathinfo($originalName, PATHINFO_EXTENSION));
    }

    /**
     * @return Collection<int, array{source: string, path: string, name: string, folder: string, size: int, modified: int, url: string, isImage: bool, deletable: bool}>
     */
    private static function scan(): Collection
    {
        $disk = self::disk();

        $uploaded = collect($disk->allFiles())
            ->filter(fn (string $path) => self::wanted($path) && ! str_contains($path, '-thumb.'))
            ->map(fn (string $path) => self::item(
                source: str_starts_with($path, 'products/') ? self::SOURCE_PRODUCT : self::SOURCE_UPLOADED,
                path: $path,
                size: (int) $disk->size($path),
                modified: (int) $disk->lastModified($path),
                url: self::absolute($disk->url($path)),
            ));

        $site = collect(File::exists(public_path('media')) ? File::allFiles(public_path('media')) : [])
            ->filter(fn (\SplFileInfo $file) => self::wanted($file->getFilename()))
            ->map(function (\SplFileInfo $file) {
                $relative = 'media/'.str_replace('\\', '/', ltrim(substr($file->getPathname(), strlen(public_path('media'))), '/\\'));

                return self::item(self::SOURCE_SITE, $relative, (int) $file->getSize(), (int) $file->getMTime(), url('/'.$relative));
            });

        return $uploaded->concat($site)->sortByDesc('modified')->values();
    }

    /**
     * @return array{source: string, path: string, name: string, folder: string, size: int, modified: int, url: string, isImage: bool, deletable: bool}
     */
    private static function item(string $source, string $path, int $size, int $modified, string $url): array
    {
        return [
            'source' => $source,
            'path' => $path,
            'name' => basename($path),
            'folder' => dirname($path) === '.' ? '' : dirname($path),
            'size' => $size,
            'modified' => $modified,
            'url' => $url,
            'isImage' => strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'pdf',
            'deletable' => $source === self::SOURCE_UPLOADED && self::isUploadedHere($path),
        ];
    }

    private static function wanted(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::EXTENSIONS, true);
    }

    private static function absolute(string $url): string
    {
        return str_starts_with($url, '/') ? url($url) : $url;
    }
}
