<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    protected $table = 'jenis';

    protected $guarded = [];

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
}
