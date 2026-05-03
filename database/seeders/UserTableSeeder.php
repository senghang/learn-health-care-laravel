<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * UserTableSeeder
 *
 * Creates clinic users and assigns them roles via the user_roles pivot.
 * Must run AFTER RbacSeeder so that roles already exist.
 */
class UserTableSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $clinicId = DB::table('clinics')->where('code', 'DTE001')->value('id');

        if (!$clinicId) {
            $this->command->warn('UserTableSeeder: clinic DTE001 not found — skipping.');
            return;
        }

        // Resolve role IDs seeded by RbacSeeder
        $adminRoleId = DB::table('roles')
            ->where('clinic_id', $clinicId)
            ->where('name', 'Admin')
            ->value('id');

        $doctorRoleId = DB::table('roles')
            ->where('clinic_id', $clinicId)
            ->where('name', 'Doctor')
            ->value('id');

        $nurseRoleId = DB::table('roles')
            ->where('clinic_id', $clinicId)
            ->where('name', 'Nurse')
            ->value('id');

        $cashierRoleId = DB::table('roles')
            ->where('clinic_id', $clinicId)
            ->where('name', 'Cashier')
            ->value('id');

        // ── Create users ──────────────────────────────────────────────────────
        $users = [
            [
                'role_id' => $adminRoleId,
                'data'    => [
                    'clinic_id'  => $clinicId,
                    'name'       => 'Admin User',
                    'email'      => 'admin@dermentro.com',
                    'password'   => Hash::make('Admin@2026'),
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
            [
                'role_id' => $doctorRoleId,
                'data'    => [
                    'clinic_id'  => $clinicId,
                    'name'       => 'Dr. Chan Sophal',
                    'email'      => 'doctor@dermentro.com',
                    'password'   => Hash::make('Doctor@2026'),
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
            [
                'role_id' => $nurseRoleId,
                'data'    => [
                    'clinic_id'  => $clinicId,
                    'name'       => 'Nurse Sreymom',
                    'email'      => 'nurse@dermentro.com',
                    'password'   => Hash::make('Nurse@2026'),
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
            [
                'role_id' => $cashierRoleId,
                'data'    => [
                    'clinic_id'  => $clinicId,
                    'name'       => 'Cashier Dara',
                    'email'      => 'cashier@dermentro.com',
                    'password'   => Hash::make('Cashier@2026'),
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ],
        ];

        foreach ($users as $entry) {
            $userId = DB::table('users')->insertGetId($entry['data']);

            // Assign role via pivot — created_by references the admin user (id=1 from first insert)
            if ($entry['role_id']) {
                DB::table('user_roles')->insert([
                    'user_id'     => $userId,
                    'role_id'     => $entry['role_id'],
                    'assigned_at' => now(),
                    'assigned_by' => null, // system seed — no acting user yet
                ]);
            }
        }

        $this->command->info("UserTableSeeder: created " . count($users) . " users for clinic {$clinicId}.");
    }
}
