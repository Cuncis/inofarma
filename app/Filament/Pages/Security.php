<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Support\AuditLogger;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * A staff member's own 2FA setup — see TwoFactorController's docblock.
 * "Aktifkan" generates a secret and recovery codes but does not turn
 * enforcement on; "Konfirmasi" is what does, once the user proves the
 * authenticator app is actually scanning it correctly.
 */
class Security extends Page
{
    protected string $view = 'filament.pages.security';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $navigationLabel = 'Keamanan';

    protected static ?string $title = 'Keamanan';

    protected static ?string $slug = 'keamanan';

    public bool $enabled = false;

    public bool $pending = false;

    public ?string $qrCodeSvg = null;

    public ?string $secretKey = null;

    /** @var list<string>|null */
    public ?array $recoveryCodes = null;

    public function mount(): void
    {
        $this->refreshState();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('activate')
                ->label('Aktifkan 2FA')
                ->icon(Heroicon::OutlinedLockClosed)
                ->visible(fn () => ! $this->enabled && ! $this->pending)
                ->action(function () {
                    $google2fa = new Google2FA;
                    $user = $this->user();

                    $user->forceFill([
                        'two_factor_secret' => $google2fa->generateSecretKey(),
                        'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
                        'two_factor_confirmed_at' => null,
                    ])->save();

                    session()->flash('two_factor_recovery_codes_reveal', $user->two_factor_recovery_codes);
                    $this->refreshState();

                    Notification::make()
                        ->success()
                        ->title('Pindai kode QR dengan aplikasi authenticator Anda, lalu konfirmasi kodenya.')
                        ->send();
                }),
            Action::make('confirm')
                ->label('Konfirmasi')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->visible(fn () => $this->pending)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Authenticator')
                        ->required()
                        ->rule(function () {
                            return function (string $attribute, $value, Closure $fail) {
                                $user = $this->user();

                                if (! $user->two_factor_secret || ! (new Google2FA)->verifyKey($user->two_factor_secret, (string) $value)) {
                                    $fail('Kode tidak valid.');
                                }
                            };
                        }),
                ])
                ->action(function () {
                    $user = $this->user();
                    $user->forceFill(['two_factor_confirmed_at' => now()])->save();

                    AuditLogger::log('2fa_confirmed', $user);
                    $this->refreshState();

                    Notification::make()->success()->title('Autentikasi dua faktor aktif.')->send();
                }),
            Action::make('regenerate')
                ->label('Buat Kode Pemulihan Baru')
                ->icon(Heroicon::OutlinedArrowPath)
                ->visible(fn () => $this->enabled)
                ->requiresConfirmation()
                ->action(function () {
                    $user = $this->user();
                    $codes = $this->generateRecoveryCodes();

                    $user->forceFill(['two_factor_recovery_codes' => $codes])->save();
                    session()->flash('two_factor_recovery_codes_reveal', $codes);
                    $this->refreshState();

                    Notification::make()->success()->title('Kode pemulihan baru telah dibuat.')->send();
                }),
            Action::make('disable')
                ->label('Nonaktifkan 2FA')
                ->icon(Heroicon::OutlinedLockOpen)
                ->color('danger')
                ->visible(fn () => $this->enabled || $this->pending)
                ->requiresConfirmation()
                ->action(function () {
                    $user = $this->user();

                    $user->forceFill([
                        'two_factor_secret' => null,
                        'two_factor_recovery_codes' => null,
                        'two_factor_confirmed_at' => null,
                    ])->save();

                    AuditLogger::log('2fa_disabled', $user);
                    $this->refreshState();

                    Notification::make()->success()->title('Autentikasi dua faktor dinonaktifkan.')->send();
                }),
        ];
    }

    private function refreshState(): void
    {
        $user = $this->user()->fresh();

        $this->pending = (bool) ($user->two_factor_secret && ! $user->two_factor_confirmed_at);
        $this->enabled = $user->hasEnabledTwoFactor();
        $this->qrCodeSvg = $this->pending
            ? (new Google2FA)->getQRCodeInline('Inofarma Admin', $user->email, $user->two_factor_secret)
            : null;
        $this->secretKey = $this->pending ? $user->two_factor_secret : null;
        $this->recoveryCodes = session('two_factor_recovery_codes_reveal');
    }

    private function user(): User
    {
        return Auth::guard('web')->user();
    }

    /**
     * @return list<string>
     */
    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }
}
