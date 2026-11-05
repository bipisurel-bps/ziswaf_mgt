<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->restrictOnDelete();
            $table->foreignId('mustahik_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->date('disbursement_date');
            $table->string('proof_document')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['campaign_id', 'disbursement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
