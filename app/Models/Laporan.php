<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporans';

    protected $guarded = [];

    public function barangs()
    {
        return $this->belongsTo(Barang::class);
    }

    public function barangmasuks()
    {
        return $this->belongsTo(Barangmasuk::class);
    }
    public function barangkeluars()
    {
        return $this->belongsTo(Barangkeluar::class);
    }
}
