<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $guarded = [];
    //relasi ke tabel barang masuk 
    public function barangmasuks()
    {
        return $this->hasmany(Barangmasuk::class);
    }
    //relasi ke tabel barang keluar
    public function barangkeluars()
    {
        return $this->hasMany(Barangkeluar::class);
    }
    //relasi ke tabel laporan
    public function laporans()
    {
        return $this->hasMany(laporan::class);
    }
    //relasi ke tabel pengajuan
    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }
    //relasi ke tabel jenis
    public function kategoris()
    {
        return $this->belongsTo(Kategori::class,'kategoris_id');
    }
    //relasi ke tabel kategori
    public function jenis()
    {
        return $this->belongsTo(jenis::class,'jenis_id');
    }
}
