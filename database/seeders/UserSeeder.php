<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@cosmorent.com',
            'password' => Hash::make('password'),
            'phone' => '123456789',
            'address' => '123 Admin Street, Anytown',
            'email_verified_at' => now(), // ← tambahkan ini
        ]);
        $adminRole = Role::where('name', 'admin')->first();
        $admin->assignRole($adminRole->name);

        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '123456789',
            'address' => '123 Main Street, Anytown',
            'email_verified_at' => now(), // ← tambahkan ini
        ]);
        $userRole = Role::where('name', 'user')->first();
        $user->assignRole($userRole->name);

        $user = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@gmail.com',
            'password' => Hash::make('password'),
            'phone' => '987654321',
            'address' => '456 Oak Avenue, Othertown',
            'email_verified_at' => now(), // ← tambahkan ini
        ]);
        $user->assignRole($userRole->name);
    }
}