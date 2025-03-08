<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BarangkeluarExporter;
use App\Filament\Resources\BarangkeluarResource\Pages;
use App\Filament\Resources\BarangkeluarResource\RelationManagers;
use App\Models\Barang;
use App\Models\Barangkeluar;
use App\Models\Pengajuan;
use Closure;
use Filament\Actions\ExportAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class BarangkeluarResource extends Resource
{
    protected static ?string $model = Barangkeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-start-on-rectangle';

    protected static ?string $navigationGroup = 'Permintaan-barang';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Barang Keluar';

    protected static ?string $slug = 'barang-keluar';

    protected static ?string $title = 'Barang keluar';


    public function getTitle(): string|Htmlable
    {
        return "Barang-keluar";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            //     Forms\Components\Select::make('pengajuan_id')
            //         ->label('Pengajuan (Opsional)')
            //         ->options(fn() => Pengajuan::query()
            //             ->where('status', 'approved')
            //             ->get()
            //             ->mapWithKeys(fn($pengajuan) => [
            //                 $pengajuan->id => "Pengajuan #{$pengajuan->id} - {$pengajuan->barangs->Nama_barang} ({$pengajuan->Jumlah_barang_diajukan} unit)"
            //             ]))
            //         ->searchable()
            //         ->reactive()
            //         ->afterStateUpdated(function ($set, $state) {
            //             if (!$state) {
            //                 $set('barang_id', null);
            //                 $set('Jumlah_barang_keluar', null);
            //                 $set('Keterangan', null);
            //                 return;
            //             }

            //             $pengajuan = Pengajuan::find($state);
            //             if (!$pengajuan) return;

            //             $set('barang_id', $pengajuan->barang_id);
            //             $set('Jumlah_barang_keluar', $pengajuan->Jumlah_barang_diajukan);
            //             $set('Keterangan', $pengajuan->keterangan);
            //         }),

            //     Forms\Components\Select::make('barang_id')
            //         ->label('Barang')
            //         ->relationship('barangs', 'Nama_barang')
            //         ->searchable()
            //         ->preload()
            //         ->required()
            //         ->reactive()
            //         ->afterStateUpdated(fn($set, $state) => $set(
            //             'max_stock',
            //             Barang::find($state)?->Jumlah_barang ?? 0
            //         )),

            //     Forms\Components\TextInput::make('Jumlah_barang_keluar')
            //         ->label('Jumlah Barang Keluar')
            //         ->numeric()
            //         ->required()
            //         ->rules([
            //             'numeric',
            //             'min:1',
            //             fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
            //                 $barang = Barang::find($get('barang_id'));
            //                 if (!$barang) return;

            //                 if ($value > $barang->Jumlah_barang) {
            //                     $fail("Stok barang tidak mencukupi. Stok tersedia: {$barang->Jumlah_barang}");
            //                 }
            //             }
            //         ]),

            //     Forms\Components\DatePicker::make('Tanggal_keluar_barang')
            //         ->label('Tanggal Keluar')
            //         ->required()
            //         ->default(now())
            //         ->native(false),

            //     Forms\Components\Textarea::make('Keterangan')
            //         ->required()
            //         ->maxLength(255),

            //     Forms\Components\Hidden::make('user_id')
            //         ->default(Filament::auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(false)
            ->columns([
                Tables\Columns\TextColumn::make('pengajuans.user.name')
                    ->label('Diajukan oleh')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('barangs.Nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('Jumlah_barang_keluar')
                    ->label('Jumlah Keluar')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('Tanggal_keluar_barang')
                    ->date()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Disetujui Oleh')
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pengajuan_status')
                    ->label('Tipe Input')
                    ->options([
                        'manual' => 'Input Manual',
                        'pengajuan' => 'Dari Pengajuan'
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'manual') {
                            return $query->whereNull('pengajuan_id');
                        }
                        if ($data['value'] === 'pengajuan') {
                            return $query->whereNotNull('pengajuan_id');
                        }
                    })
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
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
                        ->exporter(BarangkeluarExporter::class)
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBarangkeluars::route('/'),
            'create' => Pages\CreateBarangkeluar::route('/create'),
            'edit' => Pages\EditBarangkeluar::route('/{record}/edit'),
        ];
    }
}
