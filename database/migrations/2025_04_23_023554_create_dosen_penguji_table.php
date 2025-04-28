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
        Schema::create('dosen_penguji', function (Blueprint $table) {
            $table->id('id_penguji');
            $table->unsignedBigInteger('id_pengguna'); // Foreign Key ke tabel pengguna
            $table->string('nip')->unique();
            $table->string('nama');
            $table->enum('kategori', ['Penguji 1', 'Penguji 2']);
            $table->string('no_telp');
            $table->string('email')->unique();
            $table->timestamps();

             // Tambah foreign key constraint
            $table->foreign('id_pengguna')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_penguji');
    }
};
