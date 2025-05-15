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
        // Check if accommodations table already exists
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We only want to drop the table if there are no bookings referencing it
        if (!Schema::hasTable('bookings')) {
            Schema::dropIfExists('accommodations');
        }
    }
}; 