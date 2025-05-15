<?php

namespace App\Models;

use Database\Factories\AccommodationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return AccommodationFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'description',
        'capacity_min',
        'capacity_max',
        'duration_hours',
        'price',
        'available_units',
        'image_path',
        'is_active'
    ];

    /**
     * Get the bookings for this accommodation.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
} 