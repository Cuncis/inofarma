<?php

namespace App\Filament\Pages;

use App\Support\MediaLibrary;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Media File: every image the site can show, with a button to copy its link,
 * plus upload and delete for the files added here. See MediaLibrary.
 */
class MediaFiles extends Page
{
    /** Display options and how many files each shows per page. */
    public const VIEWS = ['daftar' => 30, 'kecil' => 48, 'sedang' => 24, 'besar' => 12];

    public const DEFAULT_VIEW = 'sedang';

    /** File types Media File accepts, from the upload button and from drag and drop alike. */
    public const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/avif', 'application/pdf'];

    /** Largest accepted file, in kilobytes. */
    public const MAX_SIZE = 8192;

    protected string $view = 'filament.pages.media-files';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'media-file';

    #[Url(as: 'cari')]
    public string $search = '';

    #[Url(as: 'sumber')]
    public string $source = '';

    #[Url(as: 'tampilan')]
    public string $display = self::DEFAULT_VIEW;

    public int $pageNumber = 1;

    /** @var array<int, TemporaryUploadedFile> Files dropped onto the page, waiting for saveDropped(). */
    public array $dropped = [];

    public static function getNavigationLabel(): string
    {
        return __('Media File');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Media File');
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Media:Lihat');
    }

    public function updatedSearch(): void
    {
        $this->pageNumber = 1;
    }

    public function updatedSource(): void
    {
        $this->pageNumber = 1;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function results(): Collection
    {
        return MediaLibrary::search($this->source ?: null, $this->search);
    }

    /**
     * How many files match the search in each source, for the counts on the source tabs.
     *
     * @return array<string, int>
     */
    public function sourceCounts(): array
    {
        $matches = MediaLibrary::search(null, $this->search);

        return ['' => $matches->count()] + $matches->countBy('source')->all();
    }

    public function viewMode(): string
    {
        return array_key_exists($this->display, self::VIEWS) ? $this->display : self::DEFAULT_VIEW;
    }

    public function perPage(): int
    {
        return self::VIEWS[$this->viewMode()];
    }

    public function updatedDisplay(): void
    {
        $this->pageNumber = 1;
    }

    public function pageCount(): int
    {
        return max((int) ceil($this->results()->count() / $this->perPage()), 1);
    }

    public function goToPage(int $page): void
    {
        $this->pageNumber = min(max($page, 1), $this->pageCount());
    }

    public function delete(string $path): void
    {
        if (! Auth::guard('web')->user()?->can('Media:Hapus')) {
            abort(403);
        }

        if (MediaLibrary::delete($path)) {
            Notification::make()->success()->title(__('File dihapus.'))->send();
        }
    }

    /**
     * Stores the files dragged onto the page, the same way the upload button does.
     */
    public function saveDropped(): void
    {
        if (! Auth::guard('web')->user()?->can('Media:Unggah')) {
            abort(403);
        }

        try {
            $this->validate([
                'dropped' => ['required', 'array', 'max:20'],
                'dropped.*' => ['file', 'mimetypes:'.implode(',', self::ACCEPTED_TYPES), 'max:'.self::MAX_SIZE],
            ], [
                'dropped.max' => __('Maksimal :max file sekali unggah.'),
                'dropped.*.mimetypes' => __('Hanya gambar (JPG, PNG, GIF, WebP, SVG, AVIF) dan PDF yang bisa diunggah.'),
                'dropped.*.max' => __('Ukuran file maksimal :max KB.'),
            ]);
        } catch (ValidationException $exception) {
            $this->dropped = [];

            Notification::make()->danger()->title(__('Upload gagal'))->body($exception->validator->errors()->first())->send();

            return;
        }

        foreach ($this->dropped as $file) {
            $file->storePubliclyAs(MediaLibrary::UPLOAD_DIRECTORY, MediaLibrary::storedName($file->getClientOriginalName()), config('filesystems.uploads'));
        }

        $count = count($this->dropped);
        $this->dropped = [];
        $this->pageNumber = 1;
        MediaLibrary::forget();

        Notification::make()->success()->title(__(':count file diunggah.', ['count' => $count]))->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label(__('Upload File'))
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->visible(fn () => (bool) Auth::guard('web')->user()?->can('Media:Unggah'))
                ->schema([
                    FileUpload::make('files')
                        ->label(__('File'))
                        ->multiple()
                        ->required()
                        ->acceptedFileTypes(self::ACCEPTED_TYPES)
                        ->maxSize(self::MAX_SIZE)
                        ->disk(config('filesystems.uploads'))
                        ->directory(MediaLibrary::UPLOAD_DIRECTORY)
                        ->visibility('public')
                        ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file) => MediaLibrary::storedName($file->getClientOriginalName())),
                ])
                ->action(function (array $data) {
                    MediaLibrary::forget();
                    $this->pageNumber = 1;

                    Notification::make()->success()->title(__(':count file diunggah.', ['count' => count($data['files'])]))->send();
                }),
        ];
    }
}
