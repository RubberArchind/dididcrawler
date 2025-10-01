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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('payment_date'); // tanggal pembayaran omset
            $table->decimal('total_omset', 15, 2); // total omset harian
            $table->decimal('total_fee', 15, 2); // total fee yang dipotong
            $table->decimal('net_amount', 15, 2); // total omset - total fee
            $table->decimal('paid_amount', 15, 2)->default(0); // jumlah yang sudah dibayarkan
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable(); // waktu pembayaran dilakukan
            $table->text('notes')->nullable(); // catatan pembayaran
            $table->timestamps();
            
            $table->unique(['user_id', 'payment_date']); // satu pembayaran per user per hari
            $table->index(['payment_date', 'status']); // index untuk laporan pembayaran
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
