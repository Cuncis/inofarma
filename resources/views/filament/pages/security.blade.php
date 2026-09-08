<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if ($enabled)
            <p class="text-sm font-medium text-success-600 dark:text-success-400">
                Autentikasi dua faktor aktif untuk akun Anda.
            </p>
        @elseif ($pending)
            <div class="space-y-4">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    Pindai kode QR ini dengan aplikasi authenticator Anda (Google Authenticator, Authy, dll),
                    lalu masukkan kode 6 digit yang muncul lewat tombol "Konfirmasi" di atas.
                </p>

                <div class="inline-block rounded-lg bg-white p-3">
                    {!! $qrCodeSvg !!}
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Tidak bisa memindai? Masukkan kunci ini secara manual:
                    <span class="font-mono font-semibold text-gray-950 dark:text-white">{{ $secretKey }}</span>
                </p>
            </div>
        @else
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Autentikasi dua faktor belum aktif untuk akun Anda. Tekan "Aktifkan 2FA" di atas untuk memulai.
            </p>
        @endif

        @if ($recoveryCodes)
            <div class="mt-6 rounded-lg bg-warning-50 p-4 dark:bg-warning-500/10">
                <p class="mb-2 text-sm font-semibold text-warning-700 dark:text-warning-400">
                    Simpan kode pemulihan ini di tempat aman. Setiap kode hanya bisa dipakai sekali.
                </p>

                <div class="grid grid-cols-2 gap-2 font-mono text-sm text-gray-950 sm:grid-cols-4 dark:text-white">
                    @foreach ($recoveryCodes as $code)
                        <span>{{ $code }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
