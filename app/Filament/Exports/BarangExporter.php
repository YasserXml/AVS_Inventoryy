<?php

namespace App\Filament\Exports;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Kategori;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;

class BarangExporter extends Exporter
{
    protected static ?string $model = Barang::class;
    
    // Nama file yang akan dihasilkan
    public function getFileName(Export $export): string
    {
        return 'Data Barang-' . now()->format('d-m-Y');
    }

    public function title(): string
    {
        return 'Data Barang';
    }

    // Definisi kolom export
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('Serial_number')
                ->label('Serial Number'),
            ExportColumn::make('Kode_barang')
                ->label('Kode Barang'),
            ExportColumn::make('Nama_barang')
                ->label('Nama Barang'),
            ExportColumn::make('kategoris_id')
                ->label('Kategori')
                ->formatStateUsing(
                    fn($state) => Kategori::find($state)?->Kategori_barang ?? 'N/A'
                ),
            ExportColumn::make('jenis_id')
                ->label('Jenis')
                ->formatStateUsing(
                    fn($state) => Jenis::find($state)?->Jenis_barang ?? 'N/A'
                ),
            ExportColumn::make('Jumlah_barang')
                ->label('Jumlah Barang')
                ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
            ExportColumn::make('Harga_barang')
                ->label('Harga Barang')
                ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
        ];
    }

    // Notifikasi selesai export
    public static function getCompletedNotificationBody(Export $export): string
    {
        $timestamp = now()->format('d/m/Y H:i');

        $body = "📊 Laporan Export Data Barang\n";
        $body .= "⏰ Waktu: {$timestamp}\n";
        $body .= "✅ Berhasil: " . number_format($export->successful_rows) . " data\n";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "❌ Gagal: " . number_format($failedRowsCount) . " data\n";
        }

        return $body;
    }

    // Style untuk header (ini TERSEDIA dalam API)
    public function getXlsxHeaderCellStyle(): ?Style
    {
        // Membuat border solid yang konsisten
        $headerBorder = new Border(
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_SOLID),
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_MEDIUM, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontBold()
            ->setFontSize(12)
            ->setFontName('Arial')
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor(Color::rgb(0, 112, 65)) // Warna hijau tua
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($headerBorder)
            ->setShouldWrapText(false);
    }

    // Style default untuk semua sel data (ini TERSEDIA dalam API)
    public function getXlsxCellStyle(): ?Style
    {
        // Border untuk semua sel
        $cellBorder = new Border(
            new BorderPart(Border::BOTTOM, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::LEFT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::RIGHT, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID),
            new BorderPart(Border::TOP, Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
        );

        return (new Style())
            ->setFontSize(11)
            ->setFontName('Arial')
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setBorder($cellBorder);
    }
}