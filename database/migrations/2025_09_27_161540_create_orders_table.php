<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique(); // ID order yang terklasifikasi per user
            $table->string('external_order_id')->nullable(); // ID order dari sistem eksternal
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'success'])->default('pending');
            $table->json('metadata')->nullable(); // data tambahan order
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']); // index untuk laporan per user
            $table->index('order_id'); // index untuk pencarian order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
