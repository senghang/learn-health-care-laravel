<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Central\Models\ClinicModel;
use Illuminate\Support\Facades\Hash;
use DB;

class ClinicSeeder extends Seeder
{
    public function run()
    {
        
        DB::table('clinics')->insert([

            // 'clinic_code' => 'DTE001',
            'name' => 'Derm Entro',
            // 'clinic_short_name' => 'DTE',
            // 'clinic_phone' => '012690364',
            // 'clinic_email' => 'clinic@gmail.com',
            // 'clinic_address' => 'Phnom Penh',
            'subdomain' => 'dte',
            // 'number_of_digit' => '3',
            // 'prefix_code' => 'DTE',
            // 'owner_name' => 'Hour Daney',
            // 'owner_contact' => '012 123 123',
            
            'created_at' => now(),
        ]);

        // DB::table('clinic_logos')->insert([
        //     'clinic_id' => '1',
        //     'logo_path' => '',
        //     'type' => 'dashboard',
        //     'created_at' => now(),
        // ]);
    }
}
