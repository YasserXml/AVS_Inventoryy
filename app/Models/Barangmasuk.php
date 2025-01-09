<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangmasuk extends Model
{
    protected $table = "barangmasuks";

    protected $guarded = [];
    //Relasi ke tabel laporan
    public function laporans()
    {
        return $this->hasMany(laporan::class);
    }
    //tabel barang berelasi ke tabel barang masuk 
    public function barangs()
    {
        return $this->belongsTo(Barang::class);
    }
    public function kategoris()
    {
        return $this->belongsTo(Kategori::class, 'kategoris_id');
    }
    public function jenis()
    {
        return $this->belongsTo(jenis::class, 'jenis_id');
    }
}
