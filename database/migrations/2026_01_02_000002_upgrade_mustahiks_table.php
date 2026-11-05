<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mustahiks', function (Blueprint $table) {
            $table->string('family_card_number', 30)->nullable()->after('nik');
            $table->unsignedBigInteger('income_per_month')->default(0)->after('asnaf_category');
            $table->unsignedSmallInteger('dependents_count')->default(0)->after('income_per_month');
            $table->decimal('had_kifayah_score', 8, 2)->nullable()->after('dependents_count');
            $table->string('verification_status', 20)->default('pending')->after('status_approval');
        });
    }

    public function down(): void
    {
        Schema::table('mustahiks', function (Blueprint $table) {
            $table->dropColumn([
                'family_card_number',
                'income_per_month',
                'dependents_count',
                'had_kifayah_score',
                'verification_status',
            ]);
        });
    }
};
