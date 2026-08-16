<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use App\Support\Presenters\StaffPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Staff accounts — the `User` rows behind admin sign-in, each with a branch
 * (null = pusat) and one or more roles. This is the only screen that sets
 * `users.branch_id`, which is what `BranchScope` and friends actually key
 * their confinement on (Fase 3.2).
 */
class StaffController extends Controller
{
    public function index(): Response
    {
        $staff = User::query()->with(['branch', 'roles'])->orderBy('name')->get();

        return Inertia::render('Admin/StaffList', [
            'staff' => StaffPresenter::collection($staff),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/StaffAdd', $this->formProps());
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'branch_id' => $data['branchId'] ?? null,
            'is_active' => $data['isActive'] ?? true,
        ]);
        $staff->syncRoles($data['roles'] ?? []);

        AuditLogger::log('staff_created', $staff, [], ['email' => $staff->email, 'roles' => $data['roles'] ?? []]);

        return redirect()->route('admin.staf.index')
            ->with('success', "Akun \"{$staff->name}\" berhasil ditambahkan.");
    }

    public function edit(string $staff): Response
    {
        return Inertia::render('Admin/StaffEdit', [
            'staff' => StaffPresenter::toArray($this->find($staff)),
            ...$this->formProps(),
        ]);
    }

    public function update(StaffRequest $request, string $staff): RedirectResponse
    {
        $record = $this->find($staff);
        $data = $request->validated();

        $record->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'branch_id' => $data['branchId'] ?? null,
            'is_active' => $data['isActive'] ?? true,
        ]);

        if (! empty($data['password'])) {
            $record->password = Hash::make($data['password']);
        }

        $record->save();
        $record->syncRoles($data['roles'] ?? []);

        AuditLogger::log('staff_updated', $record, [], ['email' => $record->email, 'roles' => $data['roles'] ?? []]);

        return redirect()->route('admin.staf.index')
            ->with('success', "Akun \"{$record->name}\" berhasil diperbarui.");
    }

    public function destroy(string $staff): RedirectResponse
    {
        $record = $this->find($staff);

        if ($record->id === auth('web')->id()) {
            return redirect()->route('admin.staf.index')->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $name = $record->name;
        $record->delete();

        AuditLogger::log('staff_deleted', null, ['name' => $name]);

        return redirect()->route('admin.staf.index')->with('success', "Akun \"{$name}\" berhasil dihapus.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formProps(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Branch $branch) => ['id' => $branch->id, 'name' => $branch->name])->values()->all(),
            'roles' => Role::query()->orderBy('name')->pluck('name')->values()->all(),
        ];
    }

    private function find(string $id): User
    {
        return User::where('id', $id)
            ->firstOr(fn () => abort(404, 'Akun tidak ditemukan.'));
    }
}
