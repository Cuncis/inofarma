<?php

namespace App\Filament\Resources\Newsletters;

use App\Filament\Resources\Newsletters\Pages\CreateNewsletter;
use App\Filament\Resources\Newsletters\Pages\EditNewsletter;
use App\Filament\Resources\Newsletters\Pages\ListNewsletters;
use App\Filament\Resources\Newsletters\Schemas\NewsletterForm;
use App\Filament\Resources\Newsletters\Tables\NewslettersTable;
use App\Models\Newsletter;
use App\Support\Auth\AuthorizesByPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NewsletterResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Newsletter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'newsletter';

    protected static ?int $navigationSort = -4;

    public static function getModelLabel(): string
    {
        return __('Newsletter');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Newsletter');
    }

    /**
     * A newsletter is website content mailed to every subscriber.
     *
     * @return array{viewAny: string, create?: string, update?: string, delete?: string}
     */
    protected static function permissions(): array
    {
        return [
            'viewAny' => 'Konten:Lihat',
            'create' => 'Konten:Ubah',
            'update' => 'Konten:Ubah',
            'delete' => 'Konten:Ubah',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return NewsletterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NewslettersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNewsletters::route('/'),
            'create' => CreateNewsletter::route('/create'),
            'edit' => EditNewsletter::route('/{record}/edit'),
        ];
    }
}
