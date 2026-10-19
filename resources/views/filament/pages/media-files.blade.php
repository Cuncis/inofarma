@php
    $results = $this->results();
    $counts = $this->sourceCounts();
    $pageCount = $this->pageCount();
    $current = min($this->pageNumber, $pageCount);
    $mode = $this->viewMode();
    $shown = $results->forPage($current, $this->perPage());
    $from = ($current - 1) * $this->perPage() + 1;
    $to = $from + $shown->count() - 1;
    $canDelete = (bool) auth('web')->user()?->can('Media:Hapus');
    $canUpload = (bool) auth('web')->user()?->can('Media:Unggah');
    $sources = [
        '' => __('Semua'),
        \App\Support\MediaLibrary::SOURCE_UPLOADED => __('Unggahan'),
        \App\Support\MediaLibrary::SOURCE_PRODUCT => __('Foto Produk'),
        \App\Support\MediaLibrary::SOURCE_SITE => __('Aset Situs'),
    ];
    $views = [
        'daftar' => __('Daftar'),
        'kecil' => __('Kecil'),
        'sedang' => __('Sedang'),
        'besar' => __('Besar'),
    ];
    $gridClass = [
        'kecil' => 'grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-8 2xl:grid-cols-12',
        'sedang' => 'grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-7',
        'besar' => 'grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4',
    ][$mode] ?? '';
    $badge = [
        \App\Support\MediaLibrary::SOURCE_UPLOADED => 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30',
        \App\Support\MediaLibrary::SOURCE_PRODUCT => 'bg-success-50 text-success-700 ring-success-600/20 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30',
        \App\Support\MediaLibrary::SOURCE_SITE => 'bg-gray-100 text-gray-700 ring-gray-600/20 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10',
    ];
    $checker = 'bg-[repeating-conic-gradient(#f3f4f6_0%_25%,#ffffff_0%_50%)] bg-[length:16px_16px] dark:bg-[repeating-conic-gradient(#1f2937_0%_25%,#111827_0%_50%)]';
    $formatSize = fn (int $bytes): string => $bytes >= 1048576
        ? number_format($bytes / 1048576, 1, ',', '.').' MB'
        : number_format(max($bytes / 1024, 1), 0, ',', '.').' KB';
    $details = fn (array $file): array => [
        'name' => $file['name'],
        'path' => $file['path'],
        'url' => $file['url'],
        'isImage' => $file['isImage'],
        'type' => strtoupper(pathinfo($file['name'], PATHINFO_EXTENSION)),
        'size' => $formatSize($file['size']),
        'date' => \Illuminate\Support\Carbon::createFromTimestamp($file['modified'])->translatedFormat('d M Y, H:i'),
        'source' => $sources[$file['source']],
        'deletable' => $file['deletable'] && $canDelete,
    ];
@endphp

<x-filament-panels::page>
    <div
        x-data="{
            selected: null,
            dimensions: '',
            copied: null,
            canUpload: @js($canUpload),
            dragging: false,
            uploading: false,
            progress: 0,
            upload(files) {
                if (! this.canUpload || this.uploading || ! files?.length) return
                this.uploading = true
                this.progress = 0
                this.$wire.uploadMultiple(
                    'dropped',
                    files,
                    () => this.$wire.saveDropped().finally(() => this.uploading = false),
                    () => {
                        this.uploading = false
                        new FilamentNotification().title(@js(__('Upload gagal'))).danger().send()
                    },
                    (event) => this.progress = event.detail.progress,
                )
            },
            select(file) {
                this.selected = file
                this.dimensions = ''
            },
            copy(url) {
                const done = () => {
                    this.copied = url
                    setTimeout(() => { if (this.copied === url) this.copied = null }, 1500)
                }
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(done)
                } else {
                    const box = document.createElement('textarea')
                    box.value = url
                    document.body.appendChild(box)
                    box.select()
                    document.execCommand('copy')
                    box.remove()
                    done()
                }
            },
            remove(path) {
                if (! confirm(@js(__('Hapus file ini? Halaman yang memakai tautannya akan kehilangan gambar.')))) return
                this.$wire.delete(path)
                if (this.selected?.path === path) this.selected = null
            },
        }"
        x-on:keydown.escape.window="selected = null"
        x-on:dragenter.window.prevent="if (canUpload && $event.dataTransfer.types.includes('Files')) dragging = true"
        x-on:dragover.window.prevent
        x-on:dragleave.window="if (! $event.relatedTarget) dragging = false"
        x-on:drop.window.prevent="dragging = false; upload($event.dataTransfer.files)"
        class="flex items-start gap-6"
    >
        @if ($canUpload)
            {{-- Shown while files are dragged over the window; dropping anywhere uploads them. --}}
            <div x-show="dragging" x-cloak x-transition.opacity class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center bg-primary-600/10 p-6 backdrop-blur-[2px]">
                <div class="flex h-full w-full flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-primary-500 bg-white/80 text-center dark:bg-gray-900/80">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 16V5m0 0l-4 4m4-4l4 4" stroke-linecap="round" stroke-linejoin="round" /><path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" stroke-linecap="round" /></svg>
                    </div>
                    <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('Lepaskan file untuk mengunggah') }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Gambar atau PDF, maksimal :size MB per file.', ['size' => $this::MAX_SIZE / 1024]) }}</p>
                </div>
            </div>
        @endif

        {{-- Library --}}
        <section class="relative min-w-0 flex-1 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div x-show="uploading" x-cloak class="absolute inset-x-0 top-0 z-10 h-1 bg-primary-100 dark:bg-primary-400/10" role="progressbar" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('Mengunggah…') }}">
                <div class="h-full bg-primary-600 transition-[width] duration-200" :style="`width: ${progress}%`"></div>
            </div>

            <nav class="flex gap-1 overflow-x-auto border-b border-gray-200 px-3 dark:border-white/10" aria-label="{{ __('Sumber') }}">
                @foreach ($sources as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('source', '{{ $value }}')"
                        aria-pressed="{{ $this->source === $value ? 'true' : 'false' }}"
                        @class([
                            'relative flex shrink-0 items-center gap-2 px-3 py-3 text-sm font-medium transition',
                            'text-gray-950 after:absolute after:inset-x-2 after:bottom-0 after:h-0.5 after:rounded-full after:bg-primary-600 dark:text-white' => $this->source === $value,
                            'text-gray-500 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white' => $this->source !== $value,
                        ])
                    >
                        {{ $label }}
                        <span @class([
                            'rounded-full px-1.5 py-0.5 text-[11px] font-semibold tabular-nums',
                            'bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-400' => $this->source === $value,
                            'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-400' => $this->source !== $value,
                        ])>{{ number_format($counts[$value] ?? 0, 0, ',', '.') }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="flex flex-col gap-3 border-b border-gray-200 p-3 sm:flex-row sm:items-center dark:border-white/10">
                <label class="relative flex-1">
                    <span class="sr-only">{{ __('Cari nama file…') }}</span>
                    <svg class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.5" /><path d="M20 20l-4.2-4.2" stroke-linecap="round" />
                    </svg>
                    <input
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="{{ __('Cari nama file…') }}"
                        class="w-full rounded-lg border-0 bg-white py-2 ps-9 pe-3 text-sm text-gray-950 shadow-sm ring-1 ring-gray-950/10 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10"
                    />
                </label>

                <div class="flex items-center justify-between gap-3">
                    <span x-show="uploading" x-cloak class="text-sm font-medium whitespace-nowrap text-primary-600 dark:text-primary-400" x-text="@js(__('Mengunggah…')) + ' ' + progress + '%'"></span>
                    <span x-show="! uploading" class="text-sm whitespace-nowrap text-gray-500 dark:text-gray-400" wire:loading.class="opacity-50">{{ __(':count file', ['count' => number_format($results->count(), 0, ',', '.')]) }}</span>

                    <div class="flex gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-white/5" role="group" aria-label="{{ __('Tampilan') }}">
                        @foreach ($views as $value => $label)
                            <button
                                type="button"
                                wire:click="$set('display', '{{ $value }}')"
                                title="{{ $label }}"
                                aria-label="{{ $label }}"
                                aria-pressed="{{ $mode === $value ? 'true' : 'false' }}"
                                @class([
                                    'flex h-8 w-8 items-center justify-center rounded-md transition',
                                    'bg-white text-gray-950 shadow-sm dark:bg-gray-800 dark:text-white' => $mode === $value,
                                    'text-gray-500 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white' => $mode !== $value,
                                ])
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    @switch($value)
                                        @case('daftar')
                                            <rect x="3" y="5" width="3" height="3" rx="0.8" /><rect x="8.5" y="5.5" width="12.5" height="2" rx="1" />
                                            <rect x="3" y="10.5" width="3" height="3" rx="0.8" /><rect x="8.5" y="11" width="12.5" height="2" rx="1" />
                                            <rect x="3" y="16" width="3" height="3" rx="0.8" /><rect x="8.5" y="16.5" width="12.5" height="2" rx="1" />
                                            @break
                                        @case('kecil')
                                            @foreach ([3.5, 9, 14.5] as $x)
                                                @foreach ([3.5, 9, 14.5] as $y)
                                                    <rect x="{{ $x }}" y="{{ $y }}" width="5" height="5" rx="1.2" />
                                                @endforeach
                                            @endforeach
                                            @break
                                        @case('sedang')
                                            <rect x="3.5" y="3.5" width="7.5" height="7.5" rx="1.8" /><rect x="13" y="3.5" width="7.5" height="7.5" rx="1.8" />
                                            <rect x="3.5" y="13" width="7.5" height="7.5" rx="1.8" /><rect x="13" y="13" width="7.5" height="7.5" rx="1.8" />
                                            @break
                                        @default
                                            <rect x="4" y="4" width="16" height="16" rx="3" />
                                    @endswitch
                                </svg>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="relative" wire:loading.class="opacity-60" wire:target="search, source, display, goToPage, delete">
                @if ($shown->isEmpty())
                    <div class="flex flex-col items-center gap-3 px-6 py-16 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-white/5">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" /><circle cx="9" cy="10" r="1.6" /><path d="M4 17l5-4.5 3.5 3L16 12l4 4" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->search !== '' ? __('Tidak ada file yang cocok.') : __('Belum ada file.') }}</p>
                        @if ($this->search !== '')
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Coba kata kunci lain atau pilih sumber lain.') }}</p>
                        @elseif ($canUpload)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Seret file ke sini untuk mengunggah, atau') }}</p>
                            <label class="inline-flex h-9 cursor-pointer items-center gap-1.5 rounded-lg bg-primary-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500">
                                {{ __('Pilih file') }}
                                <input type="file" multiple accept="{{ implode(',', $this::ACCEPTED_TYPES) }}" class="sr-only" x-on:change="upload($event.target.files); $event.target.value = ''" />
                            </label>
                        @endif
                    </div>
                @elseif ($mode === 'daftar')
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-xs font-medium text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">{{ __('Nama') }}</th>
                                    <th class="hidden px-4 py-2.5 font-medium md:table-cell">{{ __('Sumber') }}</th>
                                    <th class="hidden px-4 py-2.5 font-medium sm:table-cell">{{ __('Ukuran') }}</th>
                                    <th class="hidden px-4 py-2.5 font-medium lg:table-cell">{{ __('Tanggal') }}</th>
                                    <th class="px-4 py-2.5"><span class="sr-only">{{ __('Aksi') }}</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach ($shown as $file)
                                    @php($info = $details($file))
                                    <tr
                                        wire:key="media-{{ md5($file['source'].$file['path']) }}"
                                        x-on:click="select(@js($info))"
                                        class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-white/5"
                                        :class="selected?.path === @js($file['path']) && 'bg-primary-50/60 dark:bg-primary-400/5'"
                                    >
                                        <td class="px-4 py-2.5">
                                            <div class="flex min-w-0 items-center gap-3">
                                                <div class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10 {{ $checker }}">
                                                    @if ($file['isImage'])
                                                        <img src="{{ $file['url'] }}" alt="" loading="lazy" class="h-full w-full object-contain p-0.5" />
                                                    @else
                                                        <span class="text-[10px] font-bold text-danger-600">PDF</span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="max-w-[16rem] truncate font-medium text-gray-950 sm:max-w-xs dark:text-white" title="{{ $file['path'] }}">{{ $file['name'] }}</p>
                                                    <p class="max-w-[16rem] truncate text-xs text-gray-500 sm:max-w-xs dark:text-gray-400">{{ $info['type'] }}<span class="sm:hidden"> · {{ $info['size'] }}</span> · {{ $file['folder'] ?: '/' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden px-4 py-2.5 md:table-cell">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset {{ $badge[$file['source']] }}">{{ $sources[$file['source']] }}</span>
                                        </td>
                                        <td class="hidden px-4 py-2.5 whitespace-nowrap text-gray-600 tabular-nums sm:table-cell dark:text-gray-300">{{ $info['size'] }}</td>
                                        <td class="hidden px-4 py-2.5 whitespace-nowrap text-gray-600 lg:table-cell dark:text-gray-300">{{ \Illuminate\Support\Carbon::createFromTimestamp($file['modified'])->translatedFormat('d M Y') }}</td>
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center justify-end gap-1">
                                                <button
                                                    type="button"
                                                    x-on:click.stop="copy(@js($file['url']))"
                                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold whitespace-nowrap text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/5"
                                                >
                                                    <svg x-show="copied !== @js($file['url'])" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2" /><path d="M5 15V6a2 2 0 0 1 2-2h9" stroke-linecap="round" /></svg>
                                                    <svg x-show="copied === @js($file['url'])" x-cloak class="h-3.5 w-3.5 shrink-0 text-success-600 dark:text-success-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                                    <span x-show="copied !== @js($file['url'])">{{ __('Salin tautan') }}</span>
                                                    <span x-show="copied === @js($file['url'])" x-cloak class="text-success-600 dark:text-success-400">{{ __('Tersalin!') }}</span>
                                                </button>
                                                @if ($info['deletable'])
                                                    <button
                                                        type="button"
                                                        x-on:click.stop="remove(@js($file['path']))"
                                                        title="{{ __('Hapus') }}"
                                                        aria-label="{{ __('Hapus') }}"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-danger-50 hover:text-danger-600 dark:hover:bg-danger-400/10 dark:hover:text-danger-400"
                                                    >
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                            <path d="M4 7h16M10 11v6m4-6v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="grid p-4 {{ $gridClass }}">
                        @foreach ($shown as $file)
                            @php($info = $details($file))
                            <div wire:key="media-{{ md5($file['source'].$file['path']) }}" class="group relative min-w-0">
                                <button type="button" x-on:click="select(@js($info))" class="block w-full text-left focus:outline-none" title="{{ $file['path'] }}">
                                    <div
                                        class="relative flex aspect-square items-center justify-center overflow-hidden rounded-lg transition {{ $checker }}"
                                        :class="selected?.path === @js($file['path'])
                                            ? 'ring-2 ring-primary-600 ring-offset-2 ring-offset-white dark:ring-primary-500 dark:ring-offset-gray-900'
                                            : 'ring-1 ring-gray-950/10 group-hover:ring-gray-950/25 dark:ring-white/10 dark:group-hover:ring-white/30'"
                                    >
                                        @if ($file['isImage'])
                                            <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy" @class(['h-full w-full object-contain', 'p-1' => $mode === 'kecil', 'p-2' => $mode !== 'kecil']) />
                                        @else
                                            <div class="flex flex-col items-center gap-1 text-danger-600">
                                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" stroke-linejoin="round" /><path d="M14 3v5h5" stroke-linejoin="round" />
                                                </svg>
                                                <span class="text-[10px] font-bold">PDF</span>
                                            </div>
                                        @endif

                                        <span
                                            x-show="selected?.path === @js($file['path'])"
                                            x-cloak
                                            class="absolute end-1.5 top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-primary-600 text-white shadow"
                                        >
                                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                        </span>
                                    </div>

                                    @if ($mode !== 'kecil')
                                        <p class="mt-2 truncate text-xs font-medium text-gray-950 dark:text-white">{{ $file['name'] }}</p>
                                        <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">{{ $info['type'] }} · {{ $info['size'] }}</p>
                                    @endif
                                </button>

                                <button
                                    type="button"
                                    x-on:click="copy(@js($file['url']))"
                                    title="{{ __('Salin tautan') }}"
                                    aria-label="{{ __('Salin tautan') }}"
                                    @class([
                                        'absolute start-1.5 top-1.5 flex items-center justify-center rounded-md bg-white/95 text-gray-700 shadow-sm ring-1 ring-gray-950/10 transition hover:text-primary-600 focus:opacity-100 sm:opacity-0 sm:group-hover:opacity-100 dark:bg-gray-900/95 dark:text-gray-200 dark:ring-white/10',
                                        'h-6 w-6' => $mode === 'kecil',
                                        'h-7 w-7' => $mode !== 'kecil',
                                    ])
                                >
                                    <svg x-show="copied !== @js($file['url'])" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="9" y="9" width="11" height="11" rx="2" /><path d="M5 15V6a2 2 0 0 1 2-2h9" stroke-linecap="round" />
                                    </svg>
                                    <svg x-show="copied === @js($file['url'])" x-cloak class="h-3.5 w-3.5 text-success-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($shown->isNotEmpty())
                <div class="flex items-center justify-between gap-3 border-t border-gray-200 px-4 py-3 text-sm dark:border-white/10">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Menampilkan :from-:to dari :total', ['from' => $from, 'to' => $to, 'total' => number_format($results->count(), 0, ',', '.')]) }}</span>

                    @if ($pageCount > 1)
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="goToPage({{ $current - 1 }})" @disabled($current <= 1) aria-label="{{ __('Sebelumnya') }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-transparent dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/5">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                            <span class="text-gray-600 tabular-nums dark:text-gray-300">{{ $current }} / {{ $pageCount }}</span>
                            <button type="button" wire:click="goToPage({{ $current + 1 }})" @disabled($current >= $pageCount) aria-label="{{ __('Berikutnya') }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 disabled:opacity-40 disabled:hover:bg-transparent dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/5">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        {{-- File details: a side panel on large screens, a bottom sheet on small ones. --}}
        <div x-show="selected" x-cloak x-on:click="selected = null" x-transition.opacity class="fixed inset-0 z-30 bg-gray-950/40 lg:hidden"></div>

        <aside
            x-show="selected"
            x-cloak
            x-transition
            class="fixed inset-x-0 bottom-0 z-40 max-h-[85vh] overflow-y-auto rounded-t-2xl bg-white shadow-xl ring-1 ring-gray-950/5 lg:sticky lg:top-24 lg:inset-auto lg:z-auto lg:max-h-[calc(100vh-7rem)] lg:w-80 lg:shrink-0 lg:rounded-xl lg:shadow-sm xl:w-96 dark:bg-gray-900 dark:ring-white/10"
            aria-label="{{ __('Detail file') }}"
        >
            <template x-if="selected">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-white/10">
                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">{{ __('Detail file') }}</h2>
                        <button type="button" x-on:click="selected = null" aria-label="{{ __('Tutup') }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-200">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" /></svg>
                        </button>
                    </div>

                    <div class="space-y-4 p-4">
                        <a :href="selected.url" target="_blank" rel="noopener" class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10 {{ $checker }}">
                            <template x-if="selected.isImage">
                                <img :src="selected.url" :alt="selected.name" x-on:load="dimensions = $el.naturalWidth ? $el.naturalWidth + ' × ' + $el.naturalHeight + ' px' : ''" class="h-full w-full object-contain p-3" />
                            </template>
                            <template x-if="! selected.isImage">
                                <div class="flex flex-col items-center gap-2 text-danger-600">
                                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" stroke-linejoin="round" /><path d="M14 3v5h5" stroke-linejoin="round" />
                                    </svg>
                                    <span class="text-xs font-bold">PDF</span>
                                </div>
                            </template>
                        </a>

                        <p class="text-sm font-semibold break-all text-gray-950 dark:text-white" x-text="selected.name"></p>

                        <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Tipe') }}</dt>
                            <dd class="text-right text-gray-950 dark:text-white" x-text="selected.type"></dd>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Ukuran') }}</dt>
                            <dd class="text-right text-gray-950 dark:text-white" x-text="selected.size"></dd>
                            <dt x-show="dimensions" class="text-gray-500 dark:text-gray-400">{{ __('Dimensi') }}</dt>
                            <dd x-show="dimensions" class="text-right text-gray-950 dark:text-white" x-text="dimensions"></dd>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Tanggal') }}</dt>
                            <dd class="text-right text-gray-950 dark:text-white" x-text="selected.date"></dd>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Sumber') }}</dt>
                            <dd class="text-right text-gray-950 dark:text-white" x-text="selected.source"></dd>
                        </dl>

                        <div class="space-y-1.5">
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400" for="media-file-url">{{ __('Tautan file') }}</label>
                            <input
                                id="media-file-url"
                                type="text"
                                readonly
                                :value="selected.url"
                                x-on:focus="$el.select()"
                                class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 font-mono text-xs text-gray-700 ring-1 ring-gray-950/10 focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10"
                            />
                        </div>

                        <div class="grid gap-2">
                            <button type="button" x-on:click="copy(selected.url)" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 active:scale-[0.98]">
                                <svg x-show="copied !== selected.url" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2" /><path d="M5 15V6a2 2 0 0 1 2-2h9" stroke-linecap="round" /></svg>
                                <svg x-show="copied === selected.url" x-cloak class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                <span x-show="copied !== selected.url">{{ __('Salin tautan') }}</span>
                                <span x-show="copied === selected.url" x-cloak>{{ __('Tersalin!') }}</span>
                            </button>
                            <a :href="selected.url" target="_blank" rel="noopener" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-4 text-sm font-semibold text-gray-700 ring-1 ring-gray-950/10 transition hover:bg-gray-50 dark:text-gray-200 dark:ring-white/10 dark:hover:bg-white/5">
                                {{ __('Buka di tab baru') }}
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </a>
                        </div>

                        <div x-show="selected.deletable" class="border-t border-gray-200 pt-3 dark:border-white/10">
                            <button type="button" x-on:click="remove(selected.path)" class="text-sm font-medium text-danger-600 hover:underline dark:text-danger-400">{{ __('Hapus permanen') }}</button>
                        </div>
                    </div>
                </div>
            </template>
        </aside>
    </div>
</x-filament-panels::page>
