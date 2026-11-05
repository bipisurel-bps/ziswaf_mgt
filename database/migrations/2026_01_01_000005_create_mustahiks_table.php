<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mustahiks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('nik')->nullable();                 // encrypted (cast)
            $table->string('nik_hash', 64)->nullable()->unique(); // HMAC for lookup/uniqueness
            $table->string('asnaf_category', 20);
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('status_approval', 20)->default('pending');
            $table->timestamps();

            $table->index(['asnaf_category', 'status_approval']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mustahiks');
    }
};
