<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->boolean('supports_white_label')->default(false)->after('premium_protection_enabled');
            $table->string('network_name')->nullable()->after('supports_white_label');
            $table->string('brand_name')->nullable()->after('network_name');
            $table->string('brand_primary_color', 20)->default('#79bff3')->after('brand_name');
            $table->string('brand_secondary_color', 20)->default('#16324a')->after('brand_primary_color');
            $table->string('custom_domain')->nullable()->after('brand_secondary_color');
            $table->decimal('merchant_score', 5, 2)->default(0)->after('custom_domain');
            $table->timestamp('score_last_calculated_at')->nullable()->after('merchant_score');
        });

        Schema::table('laundry_orders', function (Blueprint $table): void {
            $table->string('payment_method')->default('cash')->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->timestamp('payment_paid_at')->nullable()->after('payment_reference');
            $table->boolean('pickup_delivery_opt_in')->default(false)->after('payment_paid_at');
            $table->decimal('pickup_delivery_fee', 12, 2)->default(0)->after('pickup_delivery_opt_in');
            $table->text('pickup_address')->nullable()->after('pickup_delivery_fee');
            $table->text('delivery_address')->nullable()->after('pickup_address');
            $table->unsignedInteger('loyalty_points_earned')->default(0)->after('delivery_address');
        });

        Schema::create('payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laundry_order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway_name');
            $table->string('payment_method');
            $table->string('reference')->unique();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('gateway_fee', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laundry_order_id')->constrained()->cascadeOnDelete();
            $table->string('service_type')->default('none');
            $table->string('partner_name');
            $table->string('status')->default('scheduled');
            $table->decimal('fee', 12, 2)->default(0);
            $table->timestamp('pickup_scheduled_at')->nullable();
            $table->timestamp('delivery_scheduled_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('courier_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('tier')->default('Ocean');
            $table->unsignedInteger('points_balance')->default(0);
            $table->unsignedInteger('lifetime_points')->default(0);
            $table->timestamps();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('laundry_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('earn');
            $table->integer('points');
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_accounts');
        Schema::dropIfExists('delivery_requests');
        Schema::dropIfExists('payment_transactions');

        Schema::table('laundry_orders', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_method',
                'payment_reference',
                'payment_paid_at',
                'pickup_delivery_opt_in',
                'pickup_delivery_fee',
                'pickup_address',
                'delivery_address',
                'loyalty_points_earned',
            ]);
        });

        Schema::table('laundries', function (Blueprint $table): void {
            $table->dropColumn([
                'supports_white_label',
                'network_name',
                'brand_name',
                'brand_primary_color',
                'brand_secondary_color',
                'custom_domain',
                'merchant_score',
                'score_last_calculated_at',
            ]);
        });
    }
};
