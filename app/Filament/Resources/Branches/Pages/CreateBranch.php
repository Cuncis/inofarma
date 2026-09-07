<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Models\Branch;
use App\Support\CodeSequence;
use App\Support\Slug;
use Filament\Resources\Pages\CreateRecord;

class CreateBranch extends CreateRecord
{
    protected static string $resource = BranchResource::class;

    /**
     * A new branch needs some hours to be anything but permanently "closed" —
     * this form doesn't edit the weekly schedule yet (a known gap shared with
     * the legacy admin; a proper per-day editor belongs with the rest of the
     * branch console work).
     *
     * @var array<string, array{open: string, close: string}>
     */
    private const DEFAULT_HOURS = [
        'senin' => ['open' => '08:00', 'close' => '21:00'],
        'selasa' => ['open' => '08:00', 'close' => '21:00'],
        'rabu' => ['open' => '08:00', 'close' => '21:00'],
        'kamis' => ['open' => '08:00', 'close' => '21:00'],
        'jumat' => ['open' => '08:00', 'close' => '21:00'],
        'sabtu' => ['open' => '08:00', 'close' => '21:00'],
        'minggu' => ['open' => '09:00', 'close' => '20:00'],
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = CodeSequence::next(Branch::withTrashed(), 'code', 'CB-');
        $data['slug'] = Slug::unique(Branch::withTrashed(), $data['name']);
        $data['operating_hours'] = self::DEFAULT_HOURS;
        $data['maps_url'] = self::mapsUrl($data);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function mapsUrl(array $data): ?string
    {
        if (! isset($data['latitude'], $data['longitude'])) {
            return null;
        }

        return "https://www.google.com/maps?q={$data['latitude']},{$data['longitude']}";
    }
}
