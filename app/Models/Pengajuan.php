<?php

namespace App\Models;

use App\Observers\PengajuanObserver;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuans';

   protected $fillable = [
        'barang_id',
        'user_id',
        'kategori_id',
        'jenis_id',
        'Jumlah_barang',
        'Jumlah_barang_diajukan',
        'status',
        'Tanggal_pengajuan',
        'keterangan',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason'
    ];

    protected $casts = [
        'Tanggal_pengajuan' => 'datetime',
    ];

    public function barangs(){
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function kategoris(){
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function jenis(){
        return $this->belongsTo(jenis::class, 'jenis_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejected()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
