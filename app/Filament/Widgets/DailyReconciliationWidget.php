<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Support\Money;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * "Terkumpul per Cabang per Hari" — the summary that actually matters for
 * reconciling against the bank (ReconciliationPresenter::daily()). Backed by
 * custom `->records()` since a grouped/aggregated query has no single model
 * row of its own, not by a real table of Orders.
 */
class DailyReconciliationWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Terkumpul per Cabang per Hari')
            ->description(fn () => 'Total periode ini: '.Money::rupiah($this->grandTotal()))
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filters([
                Filter::make('range')
                    ->schema([
                        DatePicker::make('dari')->label('Dari')->default(now()->subDays(6)->startOfDay()),
                        DatePicker::make('sampai')->label('Sampai')->default(now()->endOfDay()),
                    ])
                    ->columns(2),
            ])
            ->records(fn (): Collection => $this->dailyRows())
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(fn (string $state) => Carbon::parse($state)->translatedFormat('d M Y')),
                TextColumn::make('branch')
                    ->label('Cabang'),
                TextColumn::make('jumlah_pesanan')
                    ->label('Jumlah Pesanan')
                    ->alignRight(),
                TextColumn::make('total')
                    ->label('Total Terkumpul')
                    ->alignRight()
                    ->weight('bold')
                    ->formatStateUsing(fn (int $state) => Money::rupiah($state)),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada pembayaran lunas pada rentang tanggal ini.');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function dailyRows(): Collection
    {
        [$from, $to] = $this->range();

        return Order::query()
            ->with('branch')
            ->where('payment_status', 'lunas')
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw('branch_id, DATE(paid_at) as tanggal, COUNT(*) as jumlah_pesanan, SUM(grand_total) as total')
            ->groupBy('branch_id', 'tanggal')
            ->orderByDesc('tanggal')
            ->get()
            ->mapWithKeys(fn (Order $row, int $i) => [$i => [
                'branch' => $row->branch?->name ?? '—',
                'tanggal' => $row->getAttribute('tanggal'),
                'jumlah_pesanan' => (int) $row->getAttribute('jumlah_pesanan'),
                'total' => (int) $row->getAttribute('total'),
            ]]);
    }

    private function grandTotal(): int
    {
        return (int) $this->dailyRows()->sum('total');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(): array
    {
        $state = $this->getTableFilterState('range') ?? [];

        $from = filled($state['dari'] ?? null)
            ? Carbon::parse($state['dari'])->startOfDay()
            : now()->subDays(6)->startOfDay();

        $to = filled($state['sampai'] ?? null)
            ? Carbon::parse($state['sampai'])->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }
}
