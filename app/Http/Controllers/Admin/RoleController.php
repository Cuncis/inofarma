<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Support\AuditLogger;
use App\Support\PermissionCatalog;
use App\Support\Presenters\RolePresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Peran CRUD plus the Hak Akses matrix — Fase 3.2.
 *
 * A role that still has users assigned cannot be deleted, same pattern as
 * every other "can't delete while referenced" entity in this app: it forces
 * an explicit reassignment first rather than silently leaving staff with no
 * role at all.
 */
class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()->withCount(['users', 'permissions'])->orderBy('name')->get();

        return Inertia::render('Admin/RoleList', [
            'roles' => RolePresenter::collection($roles),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RoleAdd', [
            'permissionGroups' => PermissionCatalog::GROUPS,
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($data['permissions'] ?? []);

        AuditLogger::log('role_created', $role, [], $data);

        return redirect()->route('admin.peran.index')
            ->with('success', "Peran \"{$role->name}\" berhasil ditambahkan.");
    }

    public function edit(string $role): Response
    {
        $record = $this->find($role);

        return Inertia::render('Admin/RoleEdit', [
            'role' => RolePresenter::toArray($record),
            'permissionGroups' => PermissionCatalog::GROUPS,
        ]);
    }

    public function update(RoleRequest $request, string $role): RedirectResponse
    {
        $record = $this->find($role);
        $data = $request->validated();

        $record->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
        $record->syncPermissions($data['permissions'] ?? []);

        AuditLogger::log('role_updated', $record, [], $data);

        return redirect()->route('admin.peran.index')
            ->with('success', "Peran \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $role): RedirectResponse
    {
        $record = $this->find($role);
        $count = $record->users()->count();

        if ($count > 0) {
            return redirect()->route('admin.peran.index')
                ->with('error', "Peran \"{$record->name}\" masih dipakai {$count} staf dan tidak bisa dihapus.");
        }

        $name = $record->name;
        $record->delete();

        AuditLogger::log('role_deleted', null, ['name' => $name]);

        return redirect()->route('admin.peran.index')
            ->with('success', "Peran \"{$name}\" berhasil dihapus.");
    }

    /**
     * The bulk Hak Akses grid — every role's permissions saved in one request.
     */
    public function matrix(): Response
    {
        $roles = Role::query()->orderBy('name')->with('permissions')->get();

        return Inertia::render('Admin/Permissions', [
            'roles' => RolePresenter::collection($roles),
            'permissionGroups' => PermissionCatalog::GROUPS,
        ]);
    }

    public function updateMatrix(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'grants' => ['required', 'array'],
            'grants.*' => ['array'],
            'grants.*.*' => [Rule::in(PermissionCatalog::all())],
        ]);

        foreach ($data['grants'] as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            $role?->syncPermissions($permissions);
        }

        AuditLogger::log('permissions_matrix_updated');

        return redirect()->route('admin.hak-akses')->with('success', 'Hak akses berhasil disimpan.');
    }

    private function find(string $name): Role
    {
        return Role::where('name', $name)
            ->firstOr(fn () => abort(404, 'Peran tidak ditemukan.'));
    }
}
