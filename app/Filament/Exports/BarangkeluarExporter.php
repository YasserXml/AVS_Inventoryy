<?php

namespace App\Filament\Exports;

use App\Models\Barangkeluar;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BarangkeluarExporter extends Exporter
{
    protected static ?string $model = Barangkeluar::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('pengajuans.id'),
            ExportColumn::make('barangs.Nama_barang')
            ->label('Nama barang'),
            ExportColumn::make('users.name')
            ->label('Disetujui oleh'),
            ExportColumn::make('Jumlah_barang_keluar')
            ->label('Jumlah barang keluar'),
            ExportColumn::make('Tanggal_keluar_barang')
            ->label('Tanggal keluar barang'),
            ExportColumn::make('keterangan'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $timestamp = now()->format('d/m/Y H:i');

        $body = "📊 Laporan Export Data Barang Keluar\n";
        $body .= "⏰ Waktu: {$timestamp}\n";
        $body .= "✅ Berhasil: " . number_format($export->successful_rows) . " data\n";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "❌ Gagal: " . number_format($failedRowsCount) . " data\n";
        }

        return $body;
    }
}
