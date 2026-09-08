<?php

namespace App\Filament\Pages;

use App\Models\Role;
use App\Support\AuditLogger;
use App\Support\PermissionCatalog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The bulk Hak Akses grid — every role's permissions saved in one request.
 * See RoleController::matrix()/updateMatrix(). There's no Filament form
 * component for a role x permission matrix, so this renders its own table
 * rather than fighting the Schema builder for a shape it doesn't support.
 */
class PermissionMatrix extends Page
{
    protected string $view = 'filament.pages.permission-matrix';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Hak Akses';

    protected static ?string $title = 'Hak Akses';

    protected static ?string $slug = 'hak-akses';

    /** @var array<string, list<string>> */
    public array $permissionGroups = PermissionCatalog::GROUPS;

    /** @var Collection<int, Role> */
    public Collection $roles;

    /**
     * [roleName => [permissionName => bool]].
     *
     * @var array<string, array<string, bool>>
     */
    public array $grants = [];

    public function mount(): void
    {
        $this->roles = Role::query()->orderBy('name')->with('permissions')->get();

        foreach ($this->roles as $role) {
            $granted = $role->permissions->pluck('name')->all();

            foreach (PermissionCatalog::all() as $permission) {
                $this->grants[$role->name][$permission] = in_array($permission, $granted, true);
            }
        }
    }

    public static function canAccess(): bool
    {
        return (bool) Auth::guard('web')->user()?->can('Peran:Lihat');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        if (! Auth::guard('web')->user()?->can('Peran:Ubah')) {
            Notification::make()->danger()->title('Anda tidak punya izin untuk mengubah hak akses.')->send();

            return;
        }

        DB::transaction(function () {
            foreach ($this->roles as $role) {
                $permissions = array_keys(array_filter($this->grants[$role->name] ?? []));
                $role->syncPermissions($permissions);
            }
        });

        AuditLogger::log('permissions_matrix_updated');

        Notification::make()->success()->title('Hak akses berhasil disimpan.')->send();
    }
}
