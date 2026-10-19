<x-filament-panels::page>
    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Ubah gambar dan teks halaman toko tanpa mengubah kode. Pilih halaman yang ingin diubah.') }}</p>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @foreach ($this->editablePages() as $page)
            <div class="flex flex-col gap-3 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">
                        <x-filament::icon :icon="$page['icon']" class="h-5 w-5" />
                    </div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $page['title'] }}</h2>
                </div>

                <p class="flex-1 text-sm text-gray-500 dark:text-gray-400">{{ $page['description'] }}</p>

                <div class="flex flex-wrap items-center gap-3">
                    <x-filament::button tag="a" :href="$page['url']" icon="heroicon-o-pencil-square" size="sm">
                        {{ __('Ubah Konten') }}
                    </x-filament::button>
                    @if ($page['storefrontUrl'])
                        <x-filament::link :href="$page['storefrontUrl']" target="_blank" icon="heroicon-o-arrow-top-right-on-square" size="sm">
                            {{ __('Lihat halaman') }}
                        </x-filament::link>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
