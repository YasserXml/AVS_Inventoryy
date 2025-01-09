<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuans';

    protected $guarded = [];

    // protected $casts = [
    //     'approved_at' => 'datetime',
    // ];

    public function barangs(){
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function kategoris(){
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function jenis(){
        return $this->belongsTo(jenis::class, 'jenis_id');
    }

    public function Users(){
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
