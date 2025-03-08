<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BarangExporter;
use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
use App\Models\Jenis;
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
use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\BarangImporter;
use App\Models\Kategori;
use EightyNine\ExcelImport\Facades\ExcelImportAction;
use Filament\Actions\ExportAction as ActionsExportAction;
use Filament\Actions\ImportAction as ActionsImportAction;
use Filament\Support\Enums\ActionSize;
use Filament\Support\RawJs;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use GuzzleHttp\Psr7\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\RowValidator;

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

    public static function getSupportedFileTypes(): array
    {
        return [
            'csv', // Untuk CSV
            'xlsx', // Untuk XLSX
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Serial_number')
                    ->required(),
                TextInput::make('Kode_barang')
                    ->required()
                    ->numeric(),
                TextInput::make('Nama_barang')
                    ->placeholder('Masukan nama barang')
                    ->required(),
                Select::make('kategoris_id')
                    ->label('Kategori barang')
                    ->placeholder('Pilih kategori barang')
                    ->relationship('kategoris', 'Kategori_barang')
                    ->reactive() // Membuat field ini reaktif
                    ->afterStateUpdated(fn(callable $set) => $set('jenis_id', null)), // Reset jenis ketika kategori berubah
                Select::make('jenis_id')
                    ->label('Jenis barang')
                    ->placeholder('Pilih jenis barang')
                    ->options(function (callable $get) {
                        // Ambil ID kategori yang dipilih
                        $kategoriId = $get('kategoris_id');

                        // Jika belum pilih kategori, tampilkan dropdown kosong
                        if (!$kategoriId) {
                            return [];
                        }

                        // Tampilkan hanya jenis barang yang sesuai dengan kategori
                        return Jenis::where('kategori_id', $kategoriId)
                            ->pluck('Jenis_barang', 'id');
                    }),
                TextInput::make('Jumlah_barang')
                    ->numeric()
                    ->label('Jumlah barang')
                    ->required(),
                TextInput::make('Harga_barang')
                    ->numeric()
                    ->mask(RawJs::make('$money($input)'))
                    ->label('Harga barang')
                    ->prefix('Rp.')
                    ->inputMode('numeric')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $numericValue = preg_replace('/[^0-9]/', '', $state);
                        $set('Harga_barang', $numericValue);
                    }),
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
                    ->alignCenter()
                    ->label('Serial Number'),
                Tables\Columns\TextColumn::make('Kode_barang')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->alignCenter()
                    ->label('Kode Barang'),
                Tables\Columns\TextColumn::make('Nama_barang')
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->wrap()
                    ->label('Nama Barang'),
                Tables\Columns\TextColumn::make('kategoris.Kategori_barang')
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
                    ->label('Kategori Barang'),
                Tables\Columns\TextColumn::make('jenis.Jenis_barang')
                    ->searchable()
                    ->sortable()
                    ->alignCenter()
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
                    ->alignCenter()
                    ->label('Harga Barang'),
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
                    ->query(fn($query) => $query->where('Jumlah_barang', '<=', 0))
                    ->toggle(),

                Tables\Filters\Filter::make('stok_menipis')
                    ->query(fn($query) => $query->where('Jumlah_barang', '<=', 10))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('info'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->headerActions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus Data Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus data yang dipilih?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->button()
                        ->outlined(),
                    ExportBulkAction::make()
                        ->exporter(BarangExporter::class)
                        ->modalSubmitActionLabel('Export Sekarang')
                        ->modalCancelActionLabel('Batal')
                        ->modalHeading('Export Data Barang')
                        ->modalDescription('Pilih data yang akan di export.')
                        ->modalIcon('heroicon-o-document')
                        ->label('Export Data')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->button()
                        ->outlined()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data barang')
            ->emptyStateIcon('heroicon-o-inbox-stack')
            ->emptyStateDescription('Data barang akan muncul jika sudah menambahkan data barang.');
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
