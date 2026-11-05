<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursement_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 32)->unique(); // REQ-YYYYMMDD-XXXX
            $table->foreignId('campaign_id')->constrained('campaigns')->restrictOnDelete();
            $table->foreignId('mustahik_id')->constrained('mustahiks')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->text('purpose');
            $table->text('notes')->nullable();
            $table->string('proof_document')->nullable();
            $table->string('status', 20)->default('draft'); // draft, approved, disbursed, rejected
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'campaign_id']);
            $table->index('mustahik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursement_requests');
    }
};
