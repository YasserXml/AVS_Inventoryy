<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-stack';

    protected static ?string $navigationGroup = 'Master-barang';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Barang';

    protected static ?string $slug = 'barang';

    protected static ?string $title = 'Barang';

    public function getTitle(): string|Htmlable
    {
        return "Barang";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Serial_number')
                    ->required(),
                TextInput::make('Kode_barang')
                    ->required(),
                TextInput::make('Nama_barang')
                    ->placeholder('Masukan nama barang')
                    ->required(),
                Select::make('kategoris_id')
                    ->label('Kategori barang')
                    ->placeholder('Pilih kategori barang')
                    ->relationship('kategoris', 'Kategori_barang'),
                Select::make('jenis_id')
                    ->label('Jenis barang')
                    ->placeholder('Pilih jenis barang')
                    ->relationship('jenis', 'Jenis_barang'),
                TextInput::make('Jumlah_barang')
                    ->numeric()
                    ->label('Jumlah barang')
                    ->required(),
                TextInput::make('Harga_barang')
                    ->numeric()
                    ->label('Harga barang')
                    ->prefix('Rp.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Serial_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Serial Number'),
                Tables\Columns\TextColumn::make('Kode_barang')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Kode Barang'),
                Tables\Columns\TextColumn::make('Nama_barang')
                    ->searchable()
                    ->sortable()
                    ->wrap()
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
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success'
                    })
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->label('Jumlah Barang'),
                Tables\Columns\TextColumn::make('Harga_barang')
                    ->money('idr')
                    ->formatStateUsing(fn($state) => 'Rp.' . number_format($state, 0, ',', '.'))
                    ->searchable()
                    ->sortable()
                    ->alignRight()
                    ->label('Harga Barang'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Tanggal Dibuat'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Terakhir Diupdate'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategoris')
                ->label('kategori')
                ->relationship('kategoris', 'Kategori_barang')
                ->multiple()
                ->preload(),
                
            Tables\Filters\SelectFilter::make('jenis')
                ->relationship('jenis', 'Jenis_barang')
                ->multiple()
                ->preload(),
                
            Tables\Filters\Filter::make('stok_kosong')
                ->query(fn ($query) => $query->where('Jumlah_barang', '<=', 0))
                ->toggle(),
                
            Tables\Filters\Filter::make('stok_menipis')
                ->query(fn ($query) => $query->where('Jumlah_barang', '<=', 10))
                ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('info'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
