<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategoris';

    protected $fillable = [
        'Kategori_barang'
    ];


    public function barangs()
    {
        return $this->hasMany(Barang::class);
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function barangmasuks()
    {
        return $this->hasMany(Barangmasuk::class);
    }

    public function jenis()
    {
        return $this->hasMany(Jenis::class);
    }
}
