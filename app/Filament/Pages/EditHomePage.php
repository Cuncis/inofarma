<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Support\HomePageContent;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * Konten Website > Beranda: everything on the storefront home page that is
 * content rather than code. Saved through {@see HomePageContent}; the
 * category icons are saved on each `Category`.
 */
class EditHomePage extends ContentEditorPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'konten-website/beranda';

    /** What each heading sits above, for the field labels. Keys match {@see HomePageContent::HEADINGS}. */
    private const HEADING_LABELS = [
        'recommended' => 'Produk rekomendasi',
        'newArrivals' => 'Produk terbaru',
        'trending' => 'Produk terlaris',
        'brands' => 'Logo brand',
        'benefits' => 'Keuntungan',
        'nearest' => 'Apotek terdekat',
        'testimonials' => 'Testimoni',
    ];

    public static function getNavigationLabel(): string
    {
        return __('Beranda');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Konten Beranda');
    }

    public static function description(): string
    {
        return __('Hero carousel, ikon kategori, banner promo, brand, keuntungan, testimoni, dan teks penutup.');
    }

    public static function storefrontUrl(): ?string
    {
        return route('home');
    }

    protected function currentContent(): array
    {
        return [
            ...HomePageContent::all(),
            'categoryIcons' => $this->categories()->mapWithKeys(fn (Category $category) => [
                "category{$category->id}" => $category->image_path,
            ])->all(),
        ];
    }

    protected function editorComponents(): array
    {
        return [
            Tabs::make('sections')
                ->persistTabInQueryString('bagian')
                ->tabs([
                    Tab::make(__('Hero Carousel'))
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            $this->slides('hero', __('Slide besar paling atas. Ukuran ideal 1600 x 900 px (16:9).'))->minItems(1),
                        ]),
                    Tab::make(__('Ikon Kategori'))
                        ->icon(Heroicon::OutlinedSquares2x2)
                        ->schema([
                            Section::make()
                                ->description(__('Nama tiap ikon adalah nama kategorinya, jadi tautan ke toko tetap benar. Nama dan urutan diubah di menu Kategori.'))
                                ->schema([
                                    Grid::make(['default' => 2, 'md' => 4, '2xl' => 8])
                                        ->schema([
                                            ...$this->categories()->map(fn (Category $category) => $this->image("categoryIcons.category{$category->id}")
                                                ->label($category->name)
                                                ->imagePreviewHeight('120'))
                                                ->all(),
                                            $this->image('allProductsIcon')
                                                ->label(__('Semua Produk'))
                                                ->imagePreviewHeight('120'),
                                        ]),
                                ]),
                        ]),
                    Tab::make(__('Banner Promo'))
                        ->icon(Heroicon::OutlinedMegaphone)
                        ->schema([
                            $this->slides('promo', __('Banner tipis di bawah ikon kategori. Ukuran ideal 1740 x 396 px. Kosongkan untuk menyembunyikan.')),
                        ]),
                    Tab::make(__('Banner Bawah'))
                        ->icon(Heroicon::OutlinedRectangleGroup)
                        ->schema([
                            $this->slides('bottom', __('Banner setelah daftar produk. Ukuran ideal 1920 x 601 px. Kosongkan untuk menyembunyikan.')),
                        ]),
                    Tab::make(__('Brand'))
                        ->icon(Heroicon::OutlinedStar)
                        ->schema([
                            Repeater::make('brands')
                                ->hiddenLabel()
                                ->helperText(__('Logo "Brand Terlaris", ditampilkan dalam lingkaran. Pakai logo persegi.'))
                                ->schema([
                                    TextInput::make('name')->label(__('Nama Brand'))->required()->maxLength(60),
                                    $this->image('image')->label(__('Logo'))->required(),
                                ])
                                ->grid(['default' => 1, 'md' => 3, 'xl' => 4, '2xl' => 6])
                                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                ->addActionLabel(__('Tambah brand'))
                                ->reorderable()
                                ->maxItems(18),
                        ]),
                    Tab::make(__('Keuntungan'))
                        ->icon(Heroicon::OutlinedCheckBadge)
                        ->schema([
                            Repeater::make('benefits')
                                ->hiddenLabel()
                                ->helperText(__('"Keuntungan Belanja di Inofarma". Di desktop tampil 9 per baris.'))
                                ->schema([
                                    TextInput::make('label')->label(__('Judul'))->required()->maxLength(60),
                                    $this->image('image')->label(__('Ikon'))->required(),
                                ])
                                ->grid(['default' => 1, 'md' => 3, 'xl' => 4, '2xl' => 6])
                                ->itemLabel(fn (array $state) => $state['label'] ?? null)
                                ->addActionLabel(__('Tambah keuntungan'))
                                ->reorderable()
                                ->maxItems(18),
                        ]),
                    Tab::make(__('Testimoni'))
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->schema([
                            Repeater::make('testimonials')
                                ->hiddenLabel()
                                ->schema([
                                    Grid::make(['default' => 1, 'md' => 3])->schema([
                                        $this->image('image')->label(__('Foto'))->required()->avatar(),
                                        Grid::make(1)->columnSpan(['md' => 2])->schema([
                                            TextInput::make('name')->label(__('Nama'))->required()->maxLength(60),
                                            TextInput::make('branch')->label(__('Cabang'))->required()->maxLength(80),
                                        ]),
                                    ]),
                                    Textarea::make('quote')->label(__('Ulasan'))->required()->rows(3)->maxLength(600),
                                ])
                                ->grid(['default' => 1, 'lg' => 2, '2xl' => 3])
                                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                                ->addActionLabel(__('Tambah testimoni'))
                                ->reorderable()
                                ->maxItems(12),
                        ]),
                    Tab::make(__('Judul Bagian'))
                        ->icon(Heroicon::OutlinedH1)
                        ->schema([
                            Section::make()
                                ->description(__('Judul di atas tiap bagian Beranda, urut dari atas ke bawah.'))
                                ->columns(['default' => 1, 'md' => 2])
                                ->schema(collect(self::HEADING_LABELS)->map(fn (string $label, string $key) => TextInput::make("headings.{$key}")
                                    ->label(__($label))
                                    ->placeholder(HomePageContent::HEADINGS[$key])
                                    ->required()
                                    ->maxLength(80))
                                    ->values()
                                    ->all()),
                        ]),
                    Tab::make(__('Teks & Kontak'))
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([
                            Repeater::make('texts')
                                ->label(__('Teks penutup'))
                                ->schema([
                                    TextInput::make('title')->label(__('Judul'))->required()->maxLength(120),
                                    Textarea::make('body')
                                        ->label(__('Isi'))
                                        ->helperText(__('Pisahkan paragraf dengan satu baris kosong.'))
                                        ->required()
                                        ->rows(6)
                                        ->maxLength(5000),
                                ])
                                ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                ->addActionLabel(__('Tambah teks'))
                                ->reorderable()
                                ->collapsible()
                                ->maxItems(6),
                            Section::make(__('Kontak'))
                                ->schema([
                                    TextInput::make('contactTitle')->label(__('Judul'))->required()->maxLength(120),
                                    Textarea::make('contactIntro')->label(__('Pengantar'))->rows(2)->maxLength(500),
                                    Repeater::make('contacts')
                                        ->label(__('Kotak kontak'))
                                        ->schema([
                                            Select::make('icon')
                                                ->label(__('Ikon'))
                                                ->options(['phone' => __('Telepon'), 'email' => __('Email')])
                                                ->required(),
                                            TextInput::make('title')->label(__('Judul'))->required()->maxLength(120),
                                            Textarea::make('lines')
                                                ->label(__('Isi'))
                                                ->helperText(__('Satu baris per informasi.'))
                                                ->required()
                                                ->rows(3)
                                                ->maxLength(500),
                                        ])
                                        ->grid(['default' => 1, 'lg' => 2])
                                        ->itemLabel(fn (array $state) => $state['title'] ?? null)
                                        ->addActionLabel(__('Tambah kontak'))
                                        ->reorderable()
                                        ->maxItems(4),
                                ]),
                        ]),
                ]),
        ];
    }

    protected function persist(array $state): void
    {
        foreach ($this->categories() as $category) {
            $icon = $state['categoryIcons']["category{$category->id}"] ?? null;

            if (filled($icon) && HomePageContent::imageUrl($icon) !== $category->image_path) {
                HomePageContent::setCategoryIcon($category, $icon);
            }
        }

        unset($state['categoryIcons']);
        HomePageContent::save($state);
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()->orderBy('position')->orderBy('id')->get();
    }

    private function slides(string $name, string $help): Repeater
    {
        return Repeater::make($name)
            ->hiddenLabel()
            ->helperText($help)
            ->schema([
                $this->image('image')->label(__('Gambar'))->required()->imagePreviewHeight('180'),
                Grid::make(2)->schema([
                    TextInput::make('href')
                        ->label(__('Tautan'))
                        ->placeholder('/shop')
                        ->helperText(__('Halaman toko (misalnya /shop) atau alamat lengkap https://...'))
                        ->required()
                        ->maxLength(500)
                        ->regex('#^(/|https?://)#'),
                    TextInput::make('alt')
                        ->label(__('Teks alternatif'))
                        ->helperText(__('Isi gambar dalam kata-kata, untuk pembaca layar dan Google.'))
                        ->required()
                        ->maxLength(200),
                ]),
            ])
            ->grid(['default' => 1, 'lg' => 2, '2xl' => 3])
            ->itemLabel(fn (array $state) => $state['alt'] ?? null)
            ->addActionLabel(__('Tambah slide'))
            ->reorderable()
            ->maxItems(10);
    }

    /**
     * An image upload that also shows the artwork shipped under `public/media`,
     * which is not on the uploads disk. Replacing an image never deletes the
     * old file: shipped artwork belongs to the code.
     */
    private function image(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->acceptedFileTypes(HomePageContent::IMAGE_TYPES)
            ->maxSize(HomePageContent::MAX_IMAGE_SIZE)
            ->disk(config('filesystems.uploads'))
            ->directory(HomePageContent::UPLOAD_DIRECTORY)
            ->visibility('public')
            ->fetchFileInformation(false)
            ->getUploadedFileUsing(fn (string $file) => [
                'name' => basename(parse_url($file, PHP_URL_PATH) ?: $file),
                'size' => 0,
                'type' => null,
                'url' => HomePageContent::imageUrl($file),
            ])
            ->openable();
    }
}
