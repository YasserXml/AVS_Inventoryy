<?php

namespace App\Filament\Resources\BarangmasukResource\Pages;

use App\Filament\Exports\BarangmasukExporter;
use App\Filament\Resources\BarangmasukResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListBarangmasuks extends ListRecords
{
    protected static string $resource = BarangmasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('Tambah barang baru'),
            ExportAction::make()
            ->exporter(BarangmasukExporter::class)
            ->label('Export data barang masuk')
            ->color('warning'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Data barang masuk";
    }
}
