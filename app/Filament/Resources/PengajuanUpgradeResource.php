<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanUpgradeResource\Pages;
use App\Filament\Resources\PengajuanUpgradeResource\RelationManagers;
use App\Models\PengajuanUpgrade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengajuanUpgradeResource extends Resource
{
    protected static ?string $model = PengajuanUpgrade::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Permintaan-user';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengajuanUpgrades::route('/'),
            'create' => Pages\CreatePengajuanUpgrade::route('/create'),
            'edit' => Pages\EditPengajuanUpgrade::route('/{record}/edit'),
        ];
    }
}
