<?php

namespace App\Filament\Resources\BarangkeluarResource\Pages;

use App\Filament\Exports\BarangkeluarExporter;
use App\Filament\Resources\BarangkeluarResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListBarangkeluars extends ListRecords
{
    protected static string $resource = BarangkeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            // ->label('Tambah barang keluar'),
            ExportAction::make()
                ->exporter(BarangkeluarExporter::class)
                ->color('warning')
                ->label('Export data barang')
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Barang keluar";
    }
}
