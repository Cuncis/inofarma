@php($messages = $this->getRecord()->messages()->with('user')->get())

<x-filament-panels::page>
    <div wire:poll.5s="markRead" class="space-y-4">
        <div
            wire:key="thread-{{ $messages->last()?->id }}"
            x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
            class="max-h-[60vh] min-h-[16rem] space-y-3 overflow-y-auto rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            @foreach ($messages as $message)
                @php($fromAdmin = $message->isFromAdmin())

                <div class="flex {{ $fromAdmin ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%]">
                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400 {{ $fromAdmin ? 'text-right' : '' }}">
                            {{ $fromAdmin ? ($message->user?->name ?? __('Admin')) : $this->getRecord()->name }}
                            &middot; {{ $message->created_at->format('d M Y, H:i') }}
                            @if ($fromAdmin)
                                &middot;
                                {{ $message->visitor_seen_at ? __('Dibaca di chat') : ($message->emailed_at ? __('Dikirim lewat email') : __('Belum dibaca')) }}
                            @endif
                        </p>
                        <div class="whitespace-pre-wrap break-words rounded-2xl px-4 py-2.5 text-sm {{ $fromAdmin ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-950 dark:bg-white/10 dark:text-white' }}">{{ $message->body }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($this->canReply())
            <form wire:submit="send" class="space-y-3">
                <x-filament::input.wrapper :valid="! $errors->has('reply')">
                    <textarea
                        wire:model="reply"
                        x-on:keydown.ctrl.enter="$wire.send()"
                        x-on:keydown.meta.enter="$wire.send()"
                        rows="3"
                        maxlength="2000"
                        placeholder="{{ __('Tulis balasan') }}"
                        class="block w-full resize-y border-none bg-transparent px-3 py-2 text-base text-gray-950 outline-none placeholder:text-gray-400 dark:text-white sm:text-sm"
                    ></textarea>
                </x-filament::input.wrapper>

                @error('reply')
                    <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Balasan muncul di jendela chat pengunjung. Bila mereka tidak membukanya, balasan dikirim lewat email.') }}
                    </p>
                    <x-filament::button type="submit" wire:loading.attr="disabled">{{ __('Kirim Balasan') }}</x-filament::button>
                </div>
            </form>
        @endif
    </div>
</x-filament-panels::page>
