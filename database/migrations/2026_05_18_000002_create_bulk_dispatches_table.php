<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_dispatches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('channel');
            $table->text('message');
            $table->string('priority');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_dispatches');
    }
};
