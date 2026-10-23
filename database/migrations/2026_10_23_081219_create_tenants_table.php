<?php

use App\Enums\Tenant\TenantInfoStatus;
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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            // Chỉ người đã được cấp tài khoản mới có user_id.
            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->string('full_name');
            $table->string('phone', 20)->nullable()->index();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('id_number', 20)->nullable()->unique();
            $table->date('id_issued_date')->nullable();
            $table->string('id_issued_place')->nullable();
            $table->string('permanent_address', 500)->nullable();
            $table->string('occupation')->nullable();
            // Ảnh CCCD lưu ở disk private, chỉ trả về qua route có kiểm quyền.
            $table->string('id_front_path')->nullable();
            $table->string('id_back_path')->nullable();
            $table->string('info_status', 20)->default(TenantInfoStatus::Draft->value)->index();
            $table->string('info_reject_reason', 500)->nullable();
            $table->timestamp('info_verified_at')->nullable();
            $table->unsignedBigInteger('info_verified_by')->nullable();
            // Ghi chú nội bộ của chủ nhà, khách không thấy.
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
