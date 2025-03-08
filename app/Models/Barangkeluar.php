<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangkeluar extends Model
{
    protected $table = 'barangkeluars';

    protected $fillable = [
        'barang_id',
        'pengajuan_id',
        'user_id',
        'Jumlah_barang_keluar',
        'Tanggal_keluar_barang',
        'Keterangan'
    ];
    // tabel barang berelasi ke tabel barang keluar
    public function barangs()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
    public function pengajuans()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
