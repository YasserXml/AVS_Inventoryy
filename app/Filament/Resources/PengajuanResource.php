<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Models\Pengajuan;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Permintaan-user';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Pengajuan barang';

    protected static ?string $slug = 'pengajuan';

    public static function canAcces(): bool
    {
        return auth()->user()->hasAnyRole(['super_admin', 'admin', 'user']);  
    }


    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Pengajuan')
                ->description('Masukkan detail pengajuan barang')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('barang_id')
                            ->label('Pilih Barang')
                            ->relationship('barangs', 'Nama_barang')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $barang = \App\Models\Barang::find($state);
                                    if ($barang) {
                                        $set('Nama_barang', $barang->Nama_barang);
                                        $set('kategori_id', $barang->kategoris_id);
                                        $set('jenis_id', $barang->jenis_id);
                                        $set('Jumlah_barang', $barang->Jumlah_barang);
                                    }
                                }
                            })
                            ->disable(fn() => $form->getOperation() === 'edit'),
                        Forms\Components\TextInput::make('Jumlah_barang')
                            ->label('Stok Tersedia')
                            ->disabled()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($component, $state, $record, $set) {
                                if ($record && $record->barang) {
                                    $set('Jumlah_barang', $record->barang->Jumlah_barang);
                                }
                            })
                    ]),

                    Grid::make(3)->schema([
                        Forms\Components\TextInput::make('Nama_barang')
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Select::make('kategori_id')
                            ->relationship('kategoris', 'Kategori_barang')
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Select::make('jenis_id')
                            ->relationship('jenis', 'Jenis_barang')
                            ->required()
                            ->disabled()
                    ]),

                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('Jumlah_barang_diajukan')
                            ->label('Jumlah Yang Diajukan')
                            ->numeric()
                            ->required()
                            ->minValue(1),

                        Forms\Components\DatePicker::make('Tanggal_pengajuan')
                            ->required()
                            ->default(now()),
                    ]),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Keterangan Pengajuan')
                        ->required()
                        ->rows(3)
                ]),

                Section::make('Approval')
                ->description('Bagian ini hanya dapat diakses oleh admin')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak'
                        ])
                        ->disabled(fn () => !auth()->user()->hasAnyRole(['super_admin', 'admin']))
                        ->default('pending'),
            
                    Forms\Components\Textarea::make('reject_reason')
                        ->label('Alasan Penolakan')
                        ->visible(fn ($get) => $get('status') === 'rejected')
                        ->required(fn ($get) => $get('status') === 'rejected')
                ])
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'admin']))
        ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Pengaju')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('Nama_barang')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('Jumlah_barang_diajukan')
                    ->label('Jumlah'),
                
                Tables\Columns\BadgeColumn  ::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                
                Tables\Columns\TextColumn::make('Tanggal_pengajuan')
                    ->date(),
                
                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'admin']))
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('Tanggal_pengajuan')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('Tanggal_pengajuan', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('Tanggal_pengajuan', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->visible(fn (Pengajuan $record) => 
                auth()->user()->hasAnyRole(['super_admin', 'admin']) || 
                ($record->user_id === auth()->id() && $record->status === 'pending')
            ),
                Tables\Actions\Action::make('approved')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('succes')
                    ->requiresConfirmation()
                    ->visible(fn (Pengajuan $record) => 
                        auth()->user()->hasRole(['super_admin', 'admin']) && 
                        $record->status === 'pending'
                    )
                    ->visible(fn (Pengajuan $record) => 
                        auth()->user()->hasRole('super_admin') && 
                        $record->status === 'pending'
                    )
                    ->action(function (Pengajuan $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'reject_reason' => $data['reject_reason'],
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('Tanggal_pengajuan', 'desc');
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
            'index' => Pages\ListPengajuans::route('/'),
            'create' => Pages\CreatePengajuan::route('/create'),
            'edit' => Pages\EditPengajuan::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        if (!auth()->user()->hasRole('admin')) {
            $query->where('user_id', auth()->id());
        }
        
        return $query;
    }
}
