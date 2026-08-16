<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the accounts the team actually signs in with — a central Super Admin
 * plus one branch-scoped example of each role, so branch confinement
 * (Fase 3.2) has real accounts to demonstrate it against, not just factories
 * inside tests.
 */
class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'Admin Pusat',
            'email' => 'admin@inofarma.co.id',
            'password' => Hash::make('password'),
            'branch_id' => null,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        $keamanan = Branch::where('code', 'CB-001')->first();

        if (! $keamanan) {
            return;
        }

        $apj = User::factory()->create([
            'name' => 'Apoteker Cabang Keamanan',
            'email' => 'apj.cb001@inofarma.co.id',
            'password' => Hash::make('password'),
            'branch_id' => $keamanan->id,
            'is_active' => true,
        ]);
        $apj->assignRole('APJ Cabang');

        $kasir = User::factory()->create([
            'name' => 'Kasir Cabang Keamanan',
            'email' => 'kasir.cb001@inofarma.co.id',
            'password' => Hash::make('password'),
            'branch_id' => $keamanan->id,
            'is_active' => true,
        ]);
        $kasir->assignRole('Kasir');
    }
}
