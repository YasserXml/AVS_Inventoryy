<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangmasukResource\Pages;
use App\Filament\Resources\BarangmasukResource\RelationManagers;
use App\Models\Barangmasuk;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangmasukResource extends Resource
{
    protected static ?string $model = Barangmasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    protected static ?string $navigationGroup = 'Permintaan-barang';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Barang Masuk';

    protected static ?string $slug = 'barang-masuk';

    protected static ?string $title = 'Barang masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Serial_number')
                ->label('Serial number')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    //cari barang berdasarkan serial number
                    $barang = \App\Models\Barang::where('Serial_number', $state)->first();
                    //hasil pencarian berdasarkan serial number
                    if ($barang) {
                        $set('Kode_barang', $barang->Kode_barang);
                        $set('Nama_barang', $barang->Nama_barang);
                        $set('kategoris_id', $barang->kategoris_id);
                        $set('jenis_id', $barang->jenis_id);
                        $set('Harga_barang', $barang->Harga_barang);
                    }
                }),
            TextInput::make('Kode_barang')
                ->label('Kode barang')
                ->required(),
            TextInput::make('Nama_barang')
                ->label('Nama barang')
                ->placeholder('Masukan nama barang')
                ->required(),
            Select::make('kategoris_id')
                ->label('Kategori barang')
                ->relationship('kategoris', 'Kategori_barang')
                ->placeholder('Pilih kategori barang'),
            Select::make('jenis_id')
                ->label('Jenis_barang')
                ->relationship('jenis', 'Jenis_barang')
                ->placeholder('Pilih jenis barang'),
            TextInput::make('Jumlah_barang')
                ->label('Jumlah barang masuk')
                ->numeric()
                ->required(),
            TextInput::make('Harga_barang')
                ->prefix('Rp.')
                // ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 2)
                ->label('Harga barang')
                ->numeric()
                ->required(),
            DatePicker::make('Tanggal_masuk_barang')
                ->label('Tanggal barang masuk')
                ->placeholder('Input tanggal masuk')
                ->timezone('Asia/Jakarta')
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Serial_number')
                    ->searchable()
                    ->sortable()
                    ->label('Serial Number'),
                Tables\Columns\TextColumn::make('Kode_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Kode Barang'),
                Tables\Columns\TextColumn::make('Nama_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Barang'),
                Tables\Columns\TextColumn::make('kategoris.Kategori_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Kategori Barang'),
                Tables\Columns\TextColumn::make('jenis.Jenis_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Jenis Barang'),
                Tables\Columns\TextColumn::make('Jumlah_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Jumlah Barang masuk'),
                Tables\Columns\TextColumn::make('Harga_barang')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp.' . number_format($state, 0, ',', '.'))
                    ->label('Harga Barang'),
                Tables\Columns\TextColumn::make('Tanggal_masuk_barang')
                    ->searchable()
                    ->sortable()
                    ->label('Tanggal barang masuk'),
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
            'index' => Pages\ListBarangmasuks::route('/'),
            'create' => Pages\CreateBarangmasuk::route('/create'),
            'edit' => Pages\EditBarangmasuk::route('/{record}/edit'),
        ];
    }
}
