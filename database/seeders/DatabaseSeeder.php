<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admin user
        User::create([
            'firstname' => 'Admin',
            'lastname' => 'User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'contact_number' => '1234567890',
            'address' => 'Admin Address',
            'role' => 'admin'
        ]);
        
        // Create test customer
        User::create([
            'firstname' => 'Customer',
            'lastname' => 'User',
            'email' => 'customer@gmail.com',
            'password' => Hash::make('customer123'),
            'contact_number' => '9876543210',
            'address' => 'Customer Address',
            'role' => 'customer'
        ]);
        
        // Call the accommodation seeder
        $this->call([
            AccommodationSeeder::class,
        ]);
    }
}
