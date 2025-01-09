<?php

namespace App\Filament\Resources\PengajuanUpgradeResource\Pages;

use App\Filament\Resources\PengajuanUpgradeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanUpgrade extends CreateRecord
{
    protected static string $resource = PengajuanUpgradeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
