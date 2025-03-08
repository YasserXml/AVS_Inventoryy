<?php

namespace App\Filament\Resources\PengajuanUpgradeResource\Pages;

use App\Filament\Resources\PengajuanUpgradeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengajuanUpgrade extends EditRecord
{
    protected static string $resource = PengajuanUpgradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
