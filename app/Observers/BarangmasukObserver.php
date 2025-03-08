<?php

namespace App\Observers;

use App\Models\Barang;
use App\Models\Barangmasuk;

class BarangmasukObserver
{
    /**
     * Handle the Barangmasuk "created" event.
     */
    public function created(Barangmasuk $barangmasuk): void
    {
        $barangs = Barang::where('Serial_number', $barangmasuk->Serial_number)->first();
        
        if($barangs){
            $barangs->update([
                'Jumlah_barang' => $barangs->Jumlah_barang + $barangmasuk->Jumlah_barang,
            ]);
        }else{
            Barang::create([
                'Serial_number' => $barangmasuk->Serial_number,
                'Kode_barang' => $barangmasuk->Kode_barang,
                'Nama_barang' => $barangmasuk->Nama_barang,
                'Jumlah_barang' => $barangmasuk->Jumlah_barang,
                'Harga_barang' => $barangmasuk->Harga_barang,
                'kategoris_id' => $barangmasuk->kategoris_id,  
                'jenis_id' => $barangmasuk->jenis_id,    
            ]);
        }
    }

    /**
     * Handle the Barangmasuk "updated" event.
     */
    public function updated(Barangmasuk $barangmasuk): void
    {
        $barang = Barang::where('Serial_number' ,$barangmasuk->Serial_number)->first();

        if($barang){
            if($barangmasuk->wasChanged('Jumlah_barang')){
                $ubahjumlah = $barangmasuk->Jumlah_barang - $barangmasuk->getOriginal('Jumlah_barang');
                $barang->update([
                    'Jumlah_barang' => $barang->Jumlah_barang + $ubahjumlah,
                ]);
            }
            $fieldUpdate =[];
            foreach(['Kode_barang', 'Nama_barang', 'Kategori_barang', 'Jenis_barang', 'Harga_barang']as $field){
                if($barangmasuk->wasChanged($field)){
                    $fieldUpdate[$field] = $barangmasuk->$field;
                }
            }
            if(!empty($fieldUpdate)){
                $barang->update($fieldUpdate);
            }
        }
    }

    /**
     * Handle the Barangmasuk "deleted" event.
     */
    public function deleted(Barangmasuk $barangmasuk): void
    {
        //
    }

    /**
     * Handle the Barangmasuk "restored" event.
     */
    public function restored(Barangmasuk $barangmasuk): void
    {
        //
    }

    /**
     * Handle the Barangmasuk "force deleted" event.
     */
    public function forceDeleted(Barangmasuk $barangmasuk): void
    {
        //
    }
}
