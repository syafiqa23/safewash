<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->string('provider')->default('simulator')->after('laundry_order_id');
            $table->string('external_id')->nullable()->after('reference');
            $table->text('checkout_url')->nullable()->after('external_id');
            $table->string('checkout_token')->nullable()->after('checkout_url');
            $table->timestamp('expired_at')->nullable()->after('paid_at');
            $table->string('webhook_status')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table): void {
            $table->dropColumn([
                'provider',
                'external_id',
                'checkout_url',
                'checkout_token',
                'expired_at',
                'webhook_status',
            ]);
        });
    }
};
