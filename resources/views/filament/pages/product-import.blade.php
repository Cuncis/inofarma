<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if (! $result)
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Unggah berkas ekspor produk berformat Shopify (CSV). Baris dengan SKU yang sudah ada akan
                diperbarui, bukan diduplikasi.
            </p>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Produk Baru</p>
                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $result['created'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Diperbarui</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $result['updated'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Nonaktif (Perlu Peringatan)</p>
                    <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $result['warnedInactive'] }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Baris Gagal</p>
                    <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ count($result['failed']) }}</p>
                </div>
            </div>

            @if (count($result['failed']))
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">Baris yang gagal diimpor</p>
                    <ul class="space-y-1 text-sm text-danger-600 dark:text-danger-400">
                        @foreach ($result['failed'] as $failure)
                            <li>Baris {{ $failure['row'] }}: {{ $failure['message'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (count($result['imagesFailed']))
                <div class="mt-6">
                    <p class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">Gambar yang gagal diunduh</p>
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
