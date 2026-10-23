<?php

use App\Enums\Property\RoomStatus;
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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id')->index();
            $table->string('name', 50);
            $table->smallInteger('floor')->nullable();
            $table->decimal('area', 6, 2)->nullable();
            $table->unsignedTinyInteger('max_occupants')->default(1);
            // Giá thuê mặc định, VND số nguyên.
            $table->unsignedBigInteger('base_rent')->default(0);
            $table->string('status', 20)->default(RoomStatus::Vacant->value)->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
