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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('Serial_number')->unique();
            $table->integer('Kode_barang');
            $table->string('Nama_barang');
            $table->unsignedBigInteger('kategoris_id');
            $table->unsignedBigInteger('jenis_id');
            $table->integer('Jumlah_barang');
            $table->integer('Harga_barang');
            $table->timestamps();

            $table->foreign('kategoris_id')->references('id')->on('kategoris')->onDelete('cascade');
            $table->foreign('jenis_id')->references('id')->on('jenis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
