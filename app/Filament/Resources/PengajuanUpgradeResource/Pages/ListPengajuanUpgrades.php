<?php

namespace App\Filament\Resources\PengajuanUpgradeResource\Pages;

use App\Filament\Resources\PengajuanUpgradeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanUpgrades extends ListRecords
{
    protected static string $resource = PengajuanUpgradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
