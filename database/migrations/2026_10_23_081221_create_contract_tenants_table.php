<?php

use App\Enums\Contract\MemberApproval;
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
        Schema::create('contract_tenants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->index();
            $table->unsignedBigInteger('tenant_id')->index();
            // true = chủ hộ (người đứng tên hợp đồng).
            $table->boolean('is_primary')->default(false);
            $table->string('approval_status', 20)->default(MemberApproval::Approved->value)->index();
            $table->string('reject_reason', 500)->nullable();
            $table->date('joined_at');
            $table->date('left_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->unique(['contract_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_tenants');
    }
};
