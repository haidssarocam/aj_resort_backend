<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, make sure accommodations table exists
        if (!Schema::hasTable('accommodations')) {
            Schema::create('accommodations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type'); // cottage, room, tent, etc.
                $table->text('description');
                $table->integer('capacity_min');
                $table->integer('capacity_max');
                $table->integer('duration_hours');
                $table->decimal('price', 10, 2);
                $table->integer('available_units')->default(1);
                $table->string('image_path')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Now create bookings table
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('accommodation_id')->constrained()->onDelete('restrict');
            $table->string('cottage_type');
            $table->integer('duration');
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->string('payment_method');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
}; 