<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;
use Hash;

class AdminUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admin_roles')->insert([
            'name'  => 'Admin',
            'slug' => 'admin',
            'created_at' => now(),
        ]);
        DB::table('admin_users')->insert([
            //admin
            [
               
                'name' => 'Admin',
                'email'=> 'admin@gmail.com',
                'password' => Hash::make('123123'),
                'admin_role_id' => 1,
                'created_at' => now(),
                
            ],
            //user 
            [
                
                'name' => 'User',
                'email'=> 'User@gmail.com',
                'password' => Hash::make('123123'),
                'admin_role_id' => 1,
                'created_at' => now(),
                
            ]
        ]);

    }
}
