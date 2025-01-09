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
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('Serial_number')->unique();
            $table->integer('Kode_barang');
            $table->string('Nama_barang');
            $table->string('Kategori_barang');
            $table->string('Jenis_barang');
            $table->integer('Jumlah_barang');
            $table->integer('Harga_barang');
            $table->date('Tanggal_masuk_barang')->nullable();
            $table->date('Tanggal_keluar_barang')->nullable();
            $table->date('Tanggal_laporan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
