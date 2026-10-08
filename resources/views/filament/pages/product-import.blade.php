<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if (! $result)
            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <p>{{ __('Unduh template lewat tombol Export & Template, isi, lalu import. Produk dan stok dipisah dua file: produk.csv dan stok.csv.') }}</p>
                <p>{{ __('Baris produk dengan SKU yang sudah ada akan diperbarui, bukan diduplikasi. Import stok mengganti jumlah batch sesuai file.') }}</p>
            </div>
        @elseif (($result['kind'] ?? 'produk') === 'stok')
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Produk per Cabang Diperbarui') }}</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $result['groups'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Batch Diterapkan') }}</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $result['batches'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Batch Lama Dinolkan') }}</p>
                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $result['zeroed'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Baris Gagal') }}</p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ count($result['failed']) }}</p>
                </div>
            </div>

            @if (count($result['failed']))
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">{{ __('Baris yang gagal di-import') }}</p>
                    <ul class="space-y-1 text-sm text-danger-600 dark:text-danger-400">
                        @foreach ($result['failed'] as $failure)
                            <li>{{ __('Baris :row', ['row' => $failure['row']]) }}: {{ $failure['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Produk Baru') }}</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $result['created'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Diperbarui') }}</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $result['updated'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Nonaktif (Perlu Peringatan)') }}</p>
                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $result['warnedInactive'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Baris Gagal') }}</p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ count($result['failed']) }}</p>
                </div>
            </div>

            @if (count($result['failed']))
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">{{ __('Baris yang gagal di-import') }}</p>
                    <ul class="space-y-1 text-sm text-danger-600 dark:text-danger-400">
                        @foreach ($result['failed'] as $failure)
                            <li>{{ __('Baris :row', ['row' => $failure['row']]) }}: {{ $failure['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($result['imagesFailed']))
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">{{ __('Gambar yang gagal di-download') }}</p>
                    <ul class="space-y-1 text-sm text-warning-600 dark:text-warning-400">
                        @foreach ($result['imagesFailed'] as $sku)
                            <li>{{ $sku }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
