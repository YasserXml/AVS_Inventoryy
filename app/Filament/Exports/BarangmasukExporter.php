<?php

namespace App\Filament\Exports;

use App\Models\Barangmasuk;
use App\Models\Jenis;
use App\Models\Kategori;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BarangmasukExporter extends Exporter
{
    protected static ?string $model = Barangmasuk::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('Serial_number'),
            ExportColumn::make('Kode_barang'),
            ExportColumn::make('Nama_barang'),
            ExportColumn::make('kategoris_id')
                ->label('Kategori_barang')
                ->formatStateUsing(
                    fn($state) =>
                    Kategori::find($state)?->Kategori_barang ?? $state
                ),
            ExportColumn::make('jenis_id')
                ->label('Jenis_barang')
                ->formatStateUsing(
                    fn($state) =>
                    Jenis::find($state)?->Jenis_barang ?? $state
                ),
            ExportColumn::make('Jumlah_barang'),
            ExportColumn::make('Harga_barang'),
            ExportColumn::make('Tanggal_masuk_barang'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $timestamp = now()->format('d/m/Y H:i');

        $body = "📊 Laporan Export Data Barang Masuk\n";
        $body .= "⏰ Waktu: {$timestamp}\n";
        $body .= "✅ Berhasil: " . number_format($export->successful_rows) . " data\n";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "❌ Gagal: " . number_format($failedRowsCount) . " data\n";
        }

        return $body;
    }
}
