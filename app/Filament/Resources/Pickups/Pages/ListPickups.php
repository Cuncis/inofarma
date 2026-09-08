<?php

namespace App\Filament\Resources\Pickups\Pages;

use App\Filament\Resources\Pickups\PickupResource;
use Filament\Resources\Pages\ListRecords;

class ListPickups extends ListRecords
{
    protected static string $resource = PickupResource::class;

    /**
     * Read once in `mount()` and kept on the component for the "Serahkan"
     * action's `fillForm()` to read later — a Livewire action click is its
     * own follow-up request, so by then the page's original `?kode=` query
     * string is long gone from `request()`.
     */
    public ?string $prefillCode = null;

    protected function getHeaderActions(): array
    {
        return [
            // Read-only queue — an order becomes "siap diambil" from the
            // Pesanan screen (PickupCodeService::issue()), not here.
        ];
    }

    /**
     * Scanning the QR on a "siap diambil" order's slip links here with
     * `?order=&kode=` (PickupCodeService::qrSvgDataUri()) — filter the queue
     * straight to that order. The table's `$table` property isn't booted yet
     * this early in the Livewire lifecycle, so mounting the action's modal
     * automatically isn't safe to do here; the counter still presses
     * "Serahkan" themselves, same as the legacy screen.
     */
    public function mount(): void
    {
        parent::mount();

        if ($number = request()->query('order')) {
            $this->tableSearch = $number;
        }

        $this->prefillCode = request()->query('kode');
    }
}
