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
        Schema::create('bimbingan', function (Blueprint $table) {
            $table->bigIncrements('id_bimbingan'); // Primary key
            $table->unsignedBigInteger('id_ta');      // relasi ke tugas_akhir
            $table->unsignedBigInteger('id_pembimbing');   // relasi ke dosen
            $table->date('tanggal');
            $table->text('catatan');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');                $table->timestamps();
        
            // foreign key constraints (CORRECTED)
            $table->foreign('id_ta')->references('id')->on('tugas_akhir')->onDelete('cascade');
            $table->foreign('id_pembimbing')->references('id_pembimbing')->on('dosen_pembimbing')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimbingan');
    }
};
