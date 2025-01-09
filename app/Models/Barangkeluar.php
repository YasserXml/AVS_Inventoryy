<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangkeluar extends Model
{
    protected $table = 'barangkeluars';

    protected $guarded = [];
    // tabel barang berelasi ke tabel barang keluar
    public function barangs()
    {
        return $this->belongsTo(Barang::class);
    }
}
