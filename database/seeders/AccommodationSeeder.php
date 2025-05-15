<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use Illuminate\Database\Seeder;

class AccommodationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accommodations = [
            [
                'name' => 'Standard Cottage',
                'type' => 'cottage',
                'description' => 'Perfect for small groups and day trips',
                'capacity_min' => 4,
                'capacity_max' => 6,
                'duration_hours' => 3,
                'price' => 500,
                'available_units' => 10,
                'image_path' => 'accommodations/cottage-1.jpg',
                'is_active' => true,
            ],
            [
                'name' => 'Deluxe Cottage',
                'type' => 'cottage',
                'description' => 'Spacious accommodation for overnight stays',
                'capacity_min' => 6,
                'capacity_max' => 8,
                'duration_hours' => 22,
                'price' => 1500,
                'available_units' => 5,
                'image_path' => 'accommodations/cottage-2.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($accommodations as $accommodation) {
            Accommodation::create($accommodation);
        }
    }
} 