<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            'clinic_id' => 1, // make sure clinic exists
            'name'  => 'Admin',
            'created_at' => now(),
        ]);
        DB::table('users')->insert([
            //admin
            [
                'clinic_id' => 1, // make sure clinic exists
                'name' => 'Admin',
                'email'=> 'admin@gmail.com',
                'password' => Hash::make('123123'),
                'role_id' => 1,
                'created_at' => now(),
                
            ],
            //user 
            [
                'clinic_id' => 1, // make sure clinic exists
                'name' => 'User',
                'email'=> 'User@gmail.com',
                'password' => Hash::make('123123'),
                'role_id' => 1,
                'created_at' => now(),
                
            ]
        ]);

    }
}
