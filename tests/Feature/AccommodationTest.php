<?php

use App\Models\User;
use App\Models\Accommodation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an admin user
    $this->admin = User::factory()->create([
        'role' => 'admin'
    ]);

    // Create a regular user
    $this->user = User::factory()->create([
        'role' => 'customer'
    ]);
});

test('admin can view all accommodations', function () {
    // Create accommodations
    Accommodation::factory()->count(3)->create();
    
    // Act as admin and make request
    $response = $this->actingAs($this->admin)
                     ->getJson('/api/accommodations');
    
    // Assert response is successful and contains data
    $response->assertStatus(200)
             ->assertJsonCount(3, 'data');
});

test('non-admin cannot access accommodation management', function () {
    // Act as regular user and make request
    $response = $this->actingAs($this->user)
                     ->getJson('/api/accommodations');
    
    // Assert unauthorized
    $response->assertStatus(403);
});

test('admin can create a new accommodation', function () {
    // Enable fake storage
    Storage::fake('public');
    
    $accommodationData = [
        'name' => 'Luxury Suite',
        'type' => 'room',
        'description' => 'A beautiful luxury suite',
        'price' => 1000,
        'duration_hours' => 24,
        'available_units' => 5,
        'capacity_min' => 1,
        'capacity_max' => 2,
        'is_active' => true,
        'image' => UploadedFile::fake()->image('accommodation.jpg')
    ];
    
    // Act as admin and make request
    $response = $this->actingAs($this->admin)
                     ->postJson('/api/accommodations', $accommodationData);
    
    // Assert response
    $response->assertStatus(201)
             ->assertJsonPath('data.name', 'Luxury Suite')
             ->assertJsonPath('message', 'Accommodation created successfully');
    
    // Assert data was saved to database
    $this->assertDatabaseHas('accommodations', [
        'name' => 'Luxury Suite',
        'type' => 'room',
    ]);
    
    // Assert image was stored
    $accommodation = Accommodation::first();
    Storage::disk('public')->assertExists($accommodation->image_path);
});

test('admin can view a specific accommodation', function () {
    // Create an accommodation
    $accommodation = Accommodation::factory()->create();
    
    // Act as admin and make request
    $response = $this->actingAs($this->admin)
                     ->getJson("/api/accommodations/{$accommodation->id}");
    
    // Assert response
    $response->assertStatus(200)
             ->assertJsonPath('data.id', $accommodation->id)
             ->assertJsonPath('data.name', $accommodation->name);
});

test('admin can update an accommodation', function () {
    // Enable fake storage
    Storage::fake('public');
    
    // Create an accommodation
    $accommodation = Accommodation::factory()->create();
    
    $updatedData = [
        'name' => 'Updated Suite',
        'type' => 'suite',
        'description' => 'An updated description',
        'price' => 1500,
        'duration_hours' => 24,
        'available_units' => 3,
        'capacity_min' => 1,
        'capacity_max' => 3,
        'is_active' => true,
        'image' => UploadedFile::fake()->image('updated.jpg')
    ];
    
    // Act as admin and make request
    $response = $this->actingAs($this->admin)
                     ->putJson("/api/accommodations/{$accommodation->id}", $updatedData);
    
    // Assert response
    $response->assertStatus(200)
             ->assertJsonPath('data.name', 'Updated Suite')
             ->assertJsonPath('message', 'Accommodation updated successfully');
    
    // Assert data was updated in database
    $this->assertDatabaseHas('accommodations', [
        'id' => $accommodation->id,
        'name' => 'Updated Suite',
        'type' => 'suite',
    ]);
    
    // Reload the model
    $accommodation->refresh();
    Storage::disk('public')->assertExists($accommodation->image_path);
});

test('admin can delete an accommodation without bookings', function () {
    // Create an accommodation
    $accommodation = Accommodation::factory()->create();
    
    // Act as admin and make request
    $response = $this->actingAs($this->admin)
                     ->deleteJson("/api/accommodations/{$accommodation->id}");
    
    // Assert response
    $response->assertStatus(200)
             ->assertJsonPath('message', 'Accommodation deleted successfully');
    
    // Assert record was deleted
    $this->assertDatabaseMissing('accommodations', [
        'id' => $accommodation->id,
    ]);
});

test('admin cannot delete an accommodation with bookings', function () {
    // Create an accommodation with bookings
    $accommodation = Accommodation::factory()->create();
    
    // Create a booking for this accommodation using BookingFactory
    $booking = $accommodation->bookings()->create([
        'user_id' => $this->user->id,
        'cottage_type' => $accommodation->type,
        'duration' => $accommodation->duration_hours,
        'quantity' => 1,
        'total_price' => $accommodation->price,
        'payment_method' => 'cash',
        'status' => 'confirmed',
        'check_in' => now(),
        'check_out' => now()->addHours($accommodation->duration_hours),
        'number_of_guests' => 2
    ]);
    
    // Act as admin and make request
    $response = $this->actingAs($this->admin)
                     ->deleteJson("/api/accommodations/{$accommodation->id}");
    
    // Assert response
    $response->assertStatus(422)
             ->assertJsonPath('message', 'Cannot delete accommodation with existing bookings');
    
    // Assert record still exists
    $this->assertDatabaseHas('accommodations', [
        'id' => $accommodation->id,
    ]);
});

test('admin can toggle accommodation active status', function () {
    // Create an active accommodation
    $accommodation = Accommodation::factory()->create([
        'is_active' => true
    ]);
    
    // Act as admin and make request to toggle status
    $response = $this->actingAs($this->admin)
                     ->patchJson("/api/accommodations/{$accommodation->id}/toggle-active");
    
    // Assert response
    $response->assertStatus(200)
             ->assertJsonPath('message', 'Accommodation status updated successfully')
             ->assertJsonPath('data.is_active', false);
    
    // Toggle again
    $response = $this->actingAs($this->admin)
                     ->patchJson("/api/accommodations/{$accommodation->id}/toggle-active");
    
    // Assert it's toggled back
    $response->assertStatus(200)
             ->assertJsonPath('data.is_active', true);
});

test('public can view available accommodations', function () {
    // Create some accommodations
    Accommodation::factory()->count(2)->create([
        'is_active' => true,
        'available_units' => 5
    ]);
    
    Accommodation::factory()->create([
        'is_active' => false,  // Inactive accommodation
        'available_units' => 5
    ]);
    
    Accommodation::factory()->create([
        'is_active' => true, 
        'available_units' => 0  // No available units
    ]);
    
    // Make request without authentication
    $response = $this->getJson('/api/accommodations/available');
    
    // Assert response is successful and contains only active accommodations with units
    $response->assertStatus(200)
             ->assertJsonCount(2, 'data');
});

test('public can filter available accommodations', function () {
    // Create accommodations with different types and capacities
    Accommodation::factory()->create([
        'is_active' => true,
        'available_units' => 5,
        'type' => 'room',
        'capacity_min' => 1,
        'capacity_max' => 2,
        'duration_hours' => 24
    ]);
    
    Accommodation::factory()->create([
        'is_active' => true,
        'available_units' => 3,
        'type' => 'suite',
        'capacity_min' => 2,
        'capacity_max' => 4,
        'duration_hours' => 24
    ]);
    
    Accommodation::factory()->create([
        'is_active' => true,
        'available_units' => 2,
        'type' => 'cabin',
        'capacity_min' => 3,
        'capacity_max' => 6,
        'duration_hours' => 48
    ]);
    
    // Filter by type
    $typeResponse = $this->getJson('/api/accommodations/available?type=suite');
    $typeResponse->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.type', 'suite');
    
    // Filter by persons
    $personsResponse = $this->getJson('/api/accommodations/available?persons=5');
    $personsResponse->assertStatus(200)
                    ->assertJsonCount(1, 'data')
                    ->assertJsonPath('data.0.type', 'cabin');
                 
    // Filter by duration
    $durationResponse = $this->getJson('/api/accommodations/available?duration=48');
    $durationResponse->assertStatus(200)
                     ->assertJsonCount(1, 'data')
                     ->assertJsonPath('data.0.duration_hours', 48);
    
    // Multiple filters
    $multiResponse = $this->getJson('/api/accommodations/available?type=suite&persons=3');
    $multiResponse->assertStatus(200)
                  ->assertJsonCount(1, 'data')
                  ->assertJsonPath('data.0.type', 'suite');
}); 