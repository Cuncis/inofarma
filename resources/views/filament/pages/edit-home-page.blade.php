<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        @if ($this::canEdit())
            <div class="flex items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    {{ __('Simpan') }}
                </x-filament::button>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Perubahan langsung tampil di Beranda setelah disimpan.') }}</span>
            </div>
        @endif
    </form>
</x-filament-panels::page>
