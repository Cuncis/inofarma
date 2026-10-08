<?php

namespace App\Filament\Resources\Subscribers;

use App\Filament\Resources\Newsletters\NewsletterResource;
use App\Filament\Resources\Subscribers\Pages\ListSubscribers;
use App\Filament\Resources\Subscribers\Tables\SubscribersTable;
use App\Models\Subscriber;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubscriberResource extends Resource
{
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

    public static function table(Table $table): Table
    {
        return SubscribersTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscribers::route('/'),
        ];
    }
}
