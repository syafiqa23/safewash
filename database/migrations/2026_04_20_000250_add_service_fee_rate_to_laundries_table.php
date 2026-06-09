<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->decimal('service_fee_rate', 5, 2)->default(3)->after('commission_rate');
        });
    }

    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->dropColumn('service_fee_rate');
        });
    }
};
