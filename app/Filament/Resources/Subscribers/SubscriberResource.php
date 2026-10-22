<?php

namespace App\Filament\Resources\Subscribers;

use App\Filament\Resources\Newsletters\NewsletterResource;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Filament\Resources\Subscribers\Tables\SubscribersTable;
use App\Models\Subscriber;
use App\Support\Auth\AuthorizesByPermission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubscriberResource extends Resource
{
    use AuthorizesByPermission;

    protected static ?string $model = Subscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'email';

    protected static ?string $slug = 'subscriber';

    protected static ?int $navigationSort = 1;

    public static function getNavigationParentItem(): ?string
    {
        return NewsletterResource::getNavigationLabel();
    }

    public static function getModelLabel(): string
    {
        return __('Subscriber');
    }

    public static function getPluralModelLabel(): string
    {
        return __('plural:Subscriber');
    }

    /**
     * Subscribers are added by the storefront or by import, never created here.
     *
     * @return array{viewAny: string, create?: string, update?: string, delete?: string}
     */
    protected static function permissions(): array
    {
        return [
            'viewAny' => 'Konten:Lihat',
        ];
    }

    public static function table(Table $table): Table
    {
        return SubscribersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscribers::route('/'),
        ];
    }
}
