<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserTableSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin role ────────────────────────────────────────────────────────
        $roleId = DB::table('admin_roles')->insertGetId([
            'name'        => 'Super Admin',
            'slug'        => 'super-admin',
            'description' => 'Full access to all admin panel features',
            'is_system'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ── Admin panel users ─────────────────────────────────────────────────
        DB::table('admin_users')->insert([
            [
                'name'          => 'Super Admin',
                'email'         => 'admin@mediflow.app',
                'password'      => Hash::make('Admin@2026'),
                'is_active'     => true,
                'admin_role_id' => $roleId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);

        $this->command->info('AdminUserTableSeeder: super admin created.');
    }
}
