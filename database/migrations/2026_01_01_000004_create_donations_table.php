<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 32)->unique(); // INV-YYYYMMDD-XXXX
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('donor_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->unsignedSmallInteger('unique_code')->default(0);
            $table->string('donation_type', 20)->default('infaq'); // zakat/infaq/wakaf/... (akad pada kwitansi)
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_gateway_ref')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->string('payment_status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('doa_harapan')->nullable();
            $table->boolean('is_anonim')->default(false);
            $table->foreignId('fundraiser_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['payment_status', 'paid_at']);
            $table->index('campaign_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
