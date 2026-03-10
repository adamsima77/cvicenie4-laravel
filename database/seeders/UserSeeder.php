<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'first_name' => 'Pán',
                'last_name' => 'Admin',
                'email' => 'admin@ukf.sk',
                'password' => Hash::make('123'),
                'role' => 'admin',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Dávid',
                'last_name' => 'Držík',
                'email' => 'ddrzik@ukf.sk',
                'password' => Hash::make('456'),
                'role' => 'user',
                'premium_until' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Jozef',
                'last_name' => 'Kapusta',
                'email' => 'jkapusta@ukf.sk',
                'password' => Hash::make('789'),
                'role' => 'user',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Martin',
                'last_name' => 'Novák',
                'email' => 'mnovak@ukf.sk',
                'password' => Hash::make('111'),
                'role' => 'user',
                'premium_until' => now()->addDays(15),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Peter',
                'last_name' => 'Horváth',
                'email' => 'phorvath@ukf.sk',
                'password' => Hash::make('222'),
                'role' => 'user',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Lucia',
                'last_name' => 'Kováčová',
                'email' => 'lkovacova@ukf.sk',
                'password' => Hash::make('333'),
                'role' => 'user',
                'premium_until' => now()->addDays(60),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Tomáš',
                'last_name' => 'Bartoš',
                'email' => 'tbartos@ukf.sk',
                'password' => Hash::make('444'),
                'role' => 'user',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Simona',
                'last_name' => 'Urbanová',
                'email' => 'surbanova@ukf.sk',
                'password' => Hash::make('555'),
                'role' => 'user',
                'premium_until' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
