<?php

namespace App\Filament\Resources\BarangkeluarResource\Pages;

use App\Filament\Resources\BarangkeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarangkeluar extends EditRecord
{
    protected static string $resource = BarangkeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
