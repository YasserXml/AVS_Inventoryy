<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporans';

    protected $guarded = [];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function barangmasuk()
    {
        return $this->belongsTo(Barangmasuk::class);
    }

    public function barangkeluar()
    {
        return $this->belongsTo(Barangkeluar::class);
    }

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
    public function jenis()
    {
        return $this->belongsTo(Jenis::class);
    }
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
