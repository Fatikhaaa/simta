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
        Schema::create('informasi', function (Blueprint $table) {
            $table->id('id_informasi'); // Primary Key
            $table->string('judul'); // Judul informasi
            $table->text('deskripsi'); // Deskripsi informasi
            $table->timestamp('tanggal_upload')->useCurrent(); // Waktu upload otomatis

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi');
    }
};
