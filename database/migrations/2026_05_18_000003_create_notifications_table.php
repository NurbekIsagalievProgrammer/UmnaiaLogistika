<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bulk_dispatch_id')->nullable()->constrained('bulk_dispatches')->nullOnDelete();
            $table->foreignUuid('subscriber_id')->constrained('subscribers')->cascadeOnDelete();
            $table->string('channel');
            $table->string('priority');
            $table->text('message');
            $table->string('status');
            $table->string('provider_reference')->nullable();
            $table->string('failure_reason')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('dropped_at')->nullable();
            $table->timestamps();

            $table->index(['subscriber_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
