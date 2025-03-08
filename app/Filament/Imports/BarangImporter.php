<?php

namespace App\Filament\Imports;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Kategori;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelType;
use Symfony\Component\HttpFoundation\File\UploadedFile as FileUploadedFile;

class BarangImporter extends Importer
{
    protected static ?string $model = Barang::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('Serial_number')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('Kode_barang')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('Nama_barang')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('Kategori_barang')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->validate(function (string $state): bool {
                    return Kategori::where('Kategori_barang', $state)->exists();
                }, errorMessage: 'Kategori tidak ditemukan dalam database.'),
            ImportColumn::make('Jenis_barang')
                ->requiredMapping()
                ->rules(['required', 'string'])
                ->validate(function (string $state, array $state_all): bool {
                    $kategori = Kategori::where('Kategori_barang', $state_all['Kategori_barang'])->first();
                    if (!$kategori) return false;
                    
                    return Jenis::where('Jenis_barang', $state)
                        ->where('kategori_id', $kategori->id)
                        ->exists();
                }, errorMessage: 'Jenis tidak ditemukan atau tidak sesuai dengan kategori yang dipilih.'),
            ImportColumn::make('Jumlah_barang')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer', 'min:0']),
            ImportColumn::make('Harga_barang')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?Barang
    {
        // Get kategori ID
        $kategori = Kategori::where('Kategori_barang', $this->data['Kategori_barang'])->first();
        if (!$kategori) {
            return null;
        }
        
        // Get jenis ID
        $jenis = Jenis::where('Jenis_barang', $this->data['Jenis_barang'])
            ->where('kategori_id', $kategori->id)
            ->first();
        if (!$jenis) {
            return null;
        }

        return new Barang([
            'Serial_number' => $this->data['Serial_number'],
            'Kode_barang' => $this->data['Kode_barang'],
            'Nama_barang' => $this->data['Nama_barang'],
            'kategoris_id' => $kategori->id,    
            'jenis_id' => $jenis->id,          
            'Jumlah_barang' => $this->data['Jumlah_barang'],
            'Harga_barang' => $this->data['Harga_barang'],
        ]);
    }

    public function importFile($file)
    {
        $data = Excel::toArray(null, $file);

        foreach ($data[0] as $row) {
            $this->data = $row;
            $this->resolveRecord()?->save();
        }

        return new Import();
    }
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your barang import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
