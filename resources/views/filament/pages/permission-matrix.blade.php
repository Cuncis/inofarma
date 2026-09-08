<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Modul &amp; Aksi
                        </th>
                        @foreach ($roles as $role)
                            <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ $role->name }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($permissionGroups as $module => $abilities)
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <td colspan="{{ $roles->count() + 1 }}" class="px-5 py-2 text-xs font-bold text-gray-950 dark:text-white">
                                {{ $module }}
                            </td>
                        </tr>

                        @foreach ($abilities as $ability)
                            <tr wire:key="row-{{ $module }}-{{ $ability }}" class="border-b border-gray-200 last:border-0 dark:border-white/10">
                                <td class="px-5 py-2.5 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $ability }}
                                </td>

                                @foreach ($roles as $role)
                                    <td class="px-4 py-2.5 text-center">
                                        <input
                                            type="checkbox"
                                            wire:model="grants.{{ $role->name }}.{{ $module }}:{{ $ability }}"
                                            aria-label="{{ $role->name }} — {{ $module }} {{ $ability }}"
                                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600 dark:border-white/20"
                                        />
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
