<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laundry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone');
            $table->string('service_type');
            $table->decimal('weight_kg', 8, 2);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->string('status')->default('received');
            $table->string('payment_status')->default('pending');
            $table->string('tracking_code')->unique();
            $table->uuid('qr_token')->unique();
            $table->boolean('is_premium_protected')->default(false);
            $table->timestamp('promised_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('claim_status')->default('none');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_orders');
    }
};
