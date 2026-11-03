<?php

use App\Enums\Contract\ContractDocumentStatus;
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
        // Văn bản hợp đồng thuê: bản chụp thông tin lúc lập, admin sửa tự do, không đổi theo hồ sơ về sau.
        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->unique();
            $table->string('code', 50);
            $table->string('status', 20)->default(ContractDocumentStatus::Draft->value)->index();
            $table->date('signed_date');
            $table->string('signed_place', 500)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            // Bên A / Bên B: {full_name, date_of_birth, id_number, id_issued_date, id_issued_place, permanent_address, phone}
            $table->json('landlord');
            $table->json('tenant');
            // Người ở cùng: [{full_name, date_of_birth, id_number, phone}]
            $table->json('members');
            $table->string('property_address', 500);
            $table->string('room_name', 50);
            $table->decimal('room_area', 6, 2)->nullable();
            $table->unsignedTinyInteger('max_occupants')->default(1);
            $table->text('equipment')->nullable();
            // Tiền VND số nguyên.
            $table->unsignedBigInteger('rent')->default(0);
            $table->unsignedBigInteger('deposit')->default(0);
            $table->unsignedInteger('electric_price')->default(0);
            $table->unsignedInteger('water_price')->default(0);
            // Phí cố định: [{name, amount, unit}]
            $table->json('services');
            $table->unsignedTinyInteger('payment_day');
            $table->string('payment_method')->nullable();
            $table->text('extra_terms')->nullable();
            $table->string('reject_reason', 500)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_documents');
    }
};
