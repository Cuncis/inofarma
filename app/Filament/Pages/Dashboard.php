<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * Filament's dashboard with a lower navigation sort, so Pesanan, Produk and
 * Pelanggan can sit directly beneath it at the top of the menu.
 */
class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = -10;
}
