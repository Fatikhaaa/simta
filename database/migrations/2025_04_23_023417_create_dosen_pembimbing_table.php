<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dosen_pembimbing', function (Blueprint $table) {
            $table->id('id_pembimbing');
            $table->unsignedBigInteger('id_pengguna'); // Foreign key ke tabel pengguna
            $table->string('nip', 20)->unique(); 
            $table->string('nama', 100); 
            $table->enum('kategori', ['Pembimbing 1', 'Pembimbing 2']); 
            $table->string('no_telp', 15); 
            $table->string('email')->unique(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dosen_pembimbing');
    }
};