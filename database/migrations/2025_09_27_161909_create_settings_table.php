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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // kunci pengaturan
            $table->string('value'); // nilai pengaturan
            $table->string('type')->default('string'); // tipe data (string, integer, decimal, boolean)
            $table->text('description')->nullable(); // deskripsi pengaturan
            $table->string('group')->default('general'); // grup pengaturan
            $table->timestamps();
            
            $table->index('key'); // index untuk pencarian cepat
            $table->index('group'); // index untuk grup pengaturan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
