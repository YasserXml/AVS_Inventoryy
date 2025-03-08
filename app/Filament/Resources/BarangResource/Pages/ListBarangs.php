<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Exports\BarangExport;
use App\Filament\Exports\BarangExporter;
use App\Filament\Resources\BarangResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Facades\Filament;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class ListBarangs extends ListRecords
{
    protected static string $resource = BarangResource::class;

   

    protected function getHeaderActions(): array
    {
        $user = request()->user();
        
        $actions = [
            Actions\CreateAction::make()
                ->label('Tambah Barang'),
        ];

        // Cek apakah user memiliki salah satu dari role tersebut
        if ($user && ($user->hasRole('super_admin') || $user->hasRole('administrator'))) {
            $actions[] = ExportAction::make()
                ->exporter(BarangExporter::class)
                ->color('warning')
                ->label('Export data barang');
        }

        return $actions;
    }

    public function getTitle(): string|Htmlable
    {
        return "Data barang";
    }
}