<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('service_fee_rate');
        });
    }

    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
