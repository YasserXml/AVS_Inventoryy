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
        Schema::create('barangmasuks', function (Blueprint $table) {
            $table->id();
            $table->string('Serial_number');
            $table->integer('Kode_barang');
            $table->string('Nama_barang');
            $table->foreignId('kategoris_id')->constrained('kategoris');
            $table->foreignId('jenis_id')->constrained('jenis');
            $table->string('Jumlah_barang');
            $table->integer('Harga_barang');
            $table->date('Tanggal_masuk_barang');
            $table->timestamps();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangmasuks');
    }
};
