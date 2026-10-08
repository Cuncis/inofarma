@php($failures = $this->groupedFailures())

@if (count($failures))
    <div class="mt-6">
        <p class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">{{ __('Baris yang gagal di-import') }}</p>
        <ul class="space-y-2 text-sm text-danger-600 dark:text-danger-400">
            @foreach ($failures as $failure)
                <li>
                    <span class="font-medium">{{ $failure['message'] }}</span>
                    <span class="block text-xs opacity-80">
                        {{ __(':count baris', ['count' => $failure['count']]) }}:
                        {{ __('Baris :rows', ['rows' => implode(', ', $failure['rows'])]) }}@if ($failure['more'] > 0), {{ __('dan :count lainnya', ['count' => $failure['more']]) }}@endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
