<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Accommodation;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accommodation = Accommodation::factory()->create();
        $user = User::factory()->create();
        $status = fake()->randomElement(['pending', 'confirmed', 'completed', 'cancelled']);
        
        return [
            'user_id' => $user->id,
            'accommodation_id' => $accommodation->id,
            'cottage_type' => $accommodation->type,
            'duration' => $accommodation->duration_hours,
            'quantity' => fake()->numberBetween(1, 3),
            'total_price' => $accommodation->price * fake()->numberBetween(1, 3),
            'payment_method' => fake()->randomElement(['cash', 'credit_card', 'online']),
            'status' => $status,
            'check_in' => now(),
            'check_out' => now()->addHours($accommodation->duration_hours),
            'number_of_guests' => fake()->numberBetween(1, $accommodation->capacity_max)
        ];
    }
    
    /**
     * Set the booking status to pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
    
    /**
     * Set the booking status to confirmed
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }
    
    /**
     * Set the booking status to completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
    
    /**
     * Set the booking status to cancelled
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
} 