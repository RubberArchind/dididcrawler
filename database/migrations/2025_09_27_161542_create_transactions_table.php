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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique(); // ID transaksi dari webhook
            $table->decimal('amount', 15, 2); // jumlah transaksi
            $table->decimal('fee_amount', 15, 2)->default(0); // biaya MDR yang dipotong
            $table->decimal('net_amount', 15, 2); // amount - fee_amount
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable(); // metode pembayaran
            $table->json('webhook_data')->nullable(); // data lengkap dari webhook
            $table->timestamp('paid_at')->nullable(); // waktu pembayaran berhasil
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']); // index untuk laporan harian
            $table->index(['status', 'paid_at']); // index untuk filter transaksi berhasil
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
