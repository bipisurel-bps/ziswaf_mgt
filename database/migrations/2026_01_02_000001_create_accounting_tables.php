<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('type', 20); // asset, liability, equity, revenue, expense
            $table->string('fund_category', 20); // zakat, infaq_terikat, infaq_bebas, wakaf, amil, non_halal
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'fund_category']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 32)->unique(); // JE-YYYYMMDD-XXXX
            $table->date('entry_date');
            $table->nullableMorphs('reference'); // polymorphic: Donation, Disbursement, etc.
            $table->text('description');
            $table->string('status', 20)->default('posted'); // posted, draft, reversed
            $table->timestamps();

            $table->index('entry_date');
            $table->index('status');
        });

        Schema::create('journal_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('coa_account_id')->constrained('coa_accounts')->restrictOnDelete();
            $table->unsignedBigInteger('debit')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->timestamps();

            $table->index(['journal_entry_id', 'coa_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('coa_accounts');
    }
};
