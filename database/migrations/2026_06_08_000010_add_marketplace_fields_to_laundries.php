<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->string('city', 100)->nullable()->after('address');
            $table->decimal('rating', 3, 2)->default(0)->after('description');
            $table->unsignedInteger('review_count')->default(0)->after('rating');
            $table->string('operating_hours', 100)->nullable()->after('review_count');
            $table->string('photo_url', 500)->nullable()->after('operating_hours');
            $table->boolean('pickup_available')->default(false)->after('photo_url');
            $table->boolean('delivery_available')->default(false)->after('pickup_available');
        });
    }

    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table): void {
            $table->dropColumn(['city', 'rating', 'review_count', 'operating_hours', 'photo_url', 'pickup_available', 'delivery_available']);
        });
    }
};
