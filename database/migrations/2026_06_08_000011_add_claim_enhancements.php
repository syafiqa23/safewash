<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->string('claim_type', 50)->default('hilang')->after('item_name'); // hilang|rusak|tertukar
            $table->string('photo_path', 500)->nullable()->after('description');
            $table->decimal('loss_amount', 12, 2)->default(0)->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table): void {
            $table->dropColumn(['claim_type', 'photo_path', 'loss_amount']);
        });
    }
};
