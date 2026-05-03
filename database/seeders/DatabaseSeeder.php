<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed order matters:
     *   1. AdminUserTableSeeder — super-admin panel users (no FK dependencies)
     *   2. ClinicSeeder         — create the demo clinic (clinics.id = 1)
     *   3. RbacSeeder           — create roles & permissions for each clinic
     *   4. UserTableSeeder      — create clinic users + assign roles via pivot
     *   5. ProductionSeeder     — clinic settings + default print templates
     */
    public function run(): void
    {
        $this->call(AdminUserTableSeeder::class);
        $this->call(ClinicSeeder::class);
        $this->call(RbacSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(ProductionSeeder::class);
    }
}
