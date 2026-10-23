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
        Schema::create('room_meters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id');
            $table->string('type', 20);
            $table->unsignedTinyInteger('integer_digits');
            $table->unsignedTinyInteger('decimal_digits')->default(0);
            $table->decimal('initial_reading', 12, 3)->default(0);
            $table->date('installed_at');
            // null = đồng hồ đang dùng; có giá trị = đã thay.
            $table->date('replaced_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'type', 'replaced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_meters');
    }
};
