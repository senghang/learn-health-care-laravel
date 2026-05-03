<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table('clinics')->insert([
            'code'           => 'DTE001',
            'name'           => 'Derm Entro Clinic',
            'name_kh'        => 'គ្លីនិក ដឹម អ៉ីន្ត្រូ',
            'name_en'        => 'Derm Entro Clinic',
            'subdomain'      => 'dte',
            'phone'          => '012 690 364',
            'email'          => 'clinic@dermentro.com',
            'address'        => 'Phnom Penh, Cambodia',
            'owner_name'     => 'Hour Daney',
            'owner_number'   => '012 123 123',
            'start_date'     => '2024-01-01',
            'plan'           => 'standard',
            'max_users'      => 20,
            'default_locale' => 'km',
            'currency'       => 'USD',
            'is_active'      => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $this->command->info('ClinicSeeder: clinic DTE001 created.');
    }
}
