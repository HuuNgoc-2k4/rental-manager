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
        Schema::table('rooms', function (Blueprint $table) {
            // Số phòng đã thể hiện tầng; không giới hạn số người ở nữa (chỉ đếm số người đang ở).
            $table->dropColumn(['floor', 'max_occupants']);
            $table->string('room_type', 50)->nullable()->after('name');
            // Checklist nội thất hiện hành: [{name, quantity, condition, note}]
            $table->json('furniture')->nullable()->after('area');
        });

        // Bản chụp checklist lúc lập hợp đồng (biên bản bàn giao), không đổi theo phòng về sau.
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->json('equipment_items')->nullable()->after('equipment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn('equipment_items');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['room_type', 'furniture']);
            $table->smallInteger('floor')->nullable();
            $table->unsignedTinyInteger('max_occupants')->default(1);
        });
    }
};
