<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $attributes = [
        'kategoris_id' => 1,
        'jenis_id' => 1,
    ];
    protected $fillable = [
        'Serial_number',
        'Kode_barang',
        'Nama_barang',
        'kategoris_id',
        'jenis_id',
        'Jumlah_barang',
        'Harga_barang'
    ];
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
        return $this->belongsTo(Kategori::class, 'kategoris_id');
    }
    //relasi ke tabel kategori
    public function jenis()
    {
        return $this->belongsTo(jenis::class, 'jenis_id');
    }

    protected function setHargaBarangAttribute($value)
{
    // Hapus semua karakter non-numeric
    $cleanValue = preg_replace('/[^0-9]/', '', $value);
    
    // Konversi ke integer/numeric
    $this->attributes['Harga_barang'] = (int) $cleanValue;
}
}
