<?php

namespace Database\Factories;

use App\Models\Accommodation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accommodation>
 */
class AccommodationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['cottage', 'room', 'tent'];
        $durations = [3, 22]; // 3 hours or 22 hours (overnight)
        
        $capacityMin = fake()->numberBetween(1, 3);
        $capacityMax = $capacityMin + fake()->numberBetween(1, 5);
        
        return [
            'name' => fake()->unique()->words(3, true),
            'type' => fake()->randomElement($types),
            'description' => fake()->paragraph(),
            'capacity_min' => $capacityMin,
            'capacity_max' => $capacityMax,
            'duration_hours' => fake()->randomElement($durations),
            'price' => fake()->numberBetween(500, 5000),
            'available_units' => fake()->numberBetween(1, 20),
            'image_path' => 'accommodations/default.jpg',
            'is_active' => true,
        ];
    }
} 