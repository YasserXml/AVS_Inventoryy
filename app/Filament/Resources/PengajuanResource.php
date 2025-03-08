<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengajuanResource\Pages;
use App\Filament\Resources\PengajuanResource\RelationManagers;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendPengajuanNotification;
use App\Mail\PengajuanBarangNotificationEmail;
use App\Models\Barangkeluar;
use App\Models\Pengajuan;
use App\Models\User;
use App\Notifications\PengajuanNotification;
use App\Notifications\Pengajuanotification;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Spatie\Permission\Models\Role;


class PengajuanResource extends Resource
{
    protected static ?string $model = Pengajuan::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Permintaan-user';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Pengajuan Barang';

    protected static ?string $slug = 'pengajuan';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Ambil user yang sedang login
        $user = Filament::auth()->user();
        
        // Jika role-nya user, hanya tampilkan pengajuan miliknya
        if ($user->roles->pluck('name')->contains('user')) {
            $query->where('user_id', $user->id);
        }
        
        // Jika Super Admin atau Administrator, tampilkan semua
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengajuan')
                    ->description('Masukkan detail pengajuan barang')
                    ->schema([
                        Grid::make(2)->schema([
                            Hidden::make('user_id')
                                ->default(fn() => Filament::auth()->id()),
                            Forms\Components\Select::make('barang_id')
                                ->label('Pilih Barang')
                                ->relationship('barangs', 'Nama_barang')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $barangs = \App\Models\Barang::find($state);
                                        if ($barangs) {
                                            $set('Nama_barang', $barangs->Nama_barang);
                                            $set('kategori_id', $barangs->kategoris_id);
                                            $set('jenis_id', $barangs->jenis_id);
                                            $set('Jumlah_barang', $barangs->Jumlah_barang);
                                        }
                                    }
                                })
                                ->disabled(fn() => $form->getOperation() === 'edit'),
                            Forms\Components\TextInput::make('Jumlah_barang')
                                ->label('Stok Tersedia')
                                ->disabled()
                                ->default(0)
                                ->dehydrated(true)
                                ->afterStateHydrated(function ($component, $state, $record, $set) {
                                    if ($record && $record->barangs) {
                                        $set('Jumlah_barang', $record->barangs->Jumlah_barang);
                                    } else {
                                        $set('Jumlah_barang', 0);
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
                                ->label('Kategori barang')
                                ->disabled()
                                ->dehydrated(true),

                            Forms\Components\Select::make('jenis_id')
                                ->relationship('jenis', 'Jenis_barang')
                                ->required()
                                ->label('Jenis barang')
                                ->disabled()
                                ->dehydrated(true),
                        ]),

                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('Jumlah_barang_diajukan')
                                ->label('Jumlah Yang Diajukan')
                                ->numeric()
                                ->required()
                                ->minValue(1),

                            Forms\Components\DatePicker::make('Tanggal_pengajuan')
                                ->required()
                                ->default(now())
                                ->native(false),
                        ]),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Pengajuan')
                            ->required()
                            ->rows(3)
                            ->placeholder('Berikan keterangan lebih lanjut terkait pengajuan'),
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
        ->recordUrl(false) 
        ->columns([
            Tables\Columns\Layout\Stack::make([
                // Baris 1: Nama User dan Status
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('user.name')
                        ->label('')
                        ->formatStateUsing(fn ($state) => "👤 " . $state)
                        ->color('gray')
                        ->weight('bold')
                        ->searchable()
                        ->size('lg'),

                    Tables\Columns\BadgeColumn::make('status')
                        ->colors([
                            'warning' => fn ($state) => $state === 'pending',
                            'success' => fn ($state) => $state === 'approved',
                            'danger' => fn ($state) => $state === 'rejected',
                        ])
                        ->icons([
                            'heroicon-m-clock' => 'pending',
                            'heroicon-m-check-circle' => 'approved',
                            'heroicon-m-x-circle' => 'rejected',
                        ])
                        ->iconPosition('before')
                        ->size('lg'),
                ]),

                // Baris 2: Nama Barang dan Jumlah
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('barangs.Nama_barang')
                        ->label('')
                        ->weight('medium')
                        ->color('info')
                        ->size('lg'),
                    
                    Tables\Columns\TextColumn::make('Jumlah_barang_diajukan')
                        ->label('')
                        ->formatStateUsing(fn ($state) => "📦 {$state} unit")
                        ->color('gray')
                        ->size('sm'),
                ]),

                // Baris 3: Keterangan
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('')
                    ->formatStateUsing(fn ($state) => $state ? "📝 " . $state : "")
                    ->color('gray')
                    ->size('sm')
                    ->words(15)
                    ->tooltip(fn ($state) => $state),

                // Baris 4: Tanggal
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('Tanggal_pengajuan')
                        ->label('')
                        ->formatStateUsing(fn ($state) => "📅 " . date('d M Y', strtotime($state)))
                        ->color('gray')
                        ->size('sm')
                        ->sortable(),

                    // Info Approval/Reject
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('approver.name')
                            ->label('')
                            ->visible(fn (?Model $record): bool => 
                                $record instanceof Pengajuan && 
                                $record->status === 'approved' && 
                                Filament::auth()->user()->roles->first()->name === 'user'
                            )
                            ->formatStateUsing(fn ($state) => $state ? "✅ Pengajuan anda disetujui oleh: {$state}" : '')
                            ->color('success')
                            ->tooltip(fn (Pengajuan $record) => 
                                $record->approved_at 
                                    ? 'Disetujui pada ' . Carbon::parse($record->approved_at)->format('d M Y H:i')
                                    : null
                            )
                            ->size('sm'),
                    
                        Tables\Columns\TextColumn::make('rejected.name')
                            ->label('')
                            ->visible(fn (?Model $record): bool => 
                                $record instanceof Pengajuan && 
                                $record->status === 'rejected' && 
                                Filament::auth()->user()->roles->first()->name === 'user'
                            )
                            ->formatStateUsing(fn ($state) => $state ? "❌ Pengajuan anda ditolak oleh: {$state}" : '')
                            ->color('danger')
                            ->tooltip(fn (Pengajuan $record) => 
                                $record->reject_reason 
                                    ? "Alasan: {$record->reject_reason}" 
                                    : null
                            )
                            ->size('sm'),
                    ])->space(1)
                ]),
            ])->space(3),
        ])

    
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->indicator('Status'),

                Tables\Filters\Filter::make('Tanggal_pengajuan')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['from'] && !$data['until']) {
                            return null;
                        }

                        return 'Tanggal: ' . ($data['from'] ?? '...') . ' hingga ' . ($data['until'] ?? '...');
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('Tanggal_pengajuan', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('Tanggal_pengajuan', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->label('Approve')
                    ->visible(
                        fn(Pengajuan $record) =>
                        request()->user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Pengajuan')
                    ->modalDescription('Anda yakin ingin menyetujui pengajuan ini?')
                    ->modalButton('Ya, Setujui')
                    ->action(function (Pengajuan $record) {
                        if ($record->Jumlah_barang_diajukan > $record->barangs->Jumlah_barang) {
                            \Filament\Notifications\Notification::make()
                                ->title('Stok tidak mencukupi')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }
                        try {
                            DB::transaction(function () use ($record) {
                                $record->update([
                                    'status' => 'approved',
                                    'approved_by' => Filament::auth()->id(),
                                    'approved_at' => now(),
                                ]); 
                                $record->barangs->decrement('Jumlah_barang', $record->Jumlah_barang_diajukan);

                                Barangkeluar::create([
                                    'barang_id' => $record->barang_id,
                                    'pengajuan_id' => $record->id,
                                    'user_id' => Filament::auth()->id(),
                                    'Jumlah_barang_keluar' => $record->Jumlah_barang_diajukan,
                                    'Tanggal_keluar_barang' => now(),
                                    'keterangan' => $record->keterangan ?? 'Barang Keluar', 
                                ]);
                            });
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Approval Error: ' . $e->getMessage());
                            FilamentNotification::make()
                            ->danger()
                            ->title('Gagal menyetujui pengajuan')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->send();
                            
                            throw $e;
                        }
                    }),

                // Reject Action (Only for Admin/Super Admin)
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->label('Reject')
                    ->visible(
                        fn(Pengajuan $record) =>
                        request()->user()->hasAnyRole(['super_admin', 'administrator']) &&
                            $record->status === 'pending'
                    )
                    ->form([
                        Forms\Components\Textarea::make('reject_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255)
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $record->update([
                                'status' => 'rejected',
                                'rejected_by' => Filament::auth()->id(),
                                'rejected_at' => now(),
                                'reject_reason' => $data['reject_reason']
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error rejecting pengajuan', [
                                'message' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);

                            FilamentNotification::make()
                                ->danger()
                                ->title('Gagal menolak pengajuan')
                                ->send();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('10s') // Auto refresh setiap 10 detik
            ->striped()
            ->emptyStateHeading('Belum ada pengajuan')
            ->emptyStateDescription('Pengajuan akan muncul di sini ketika ada yang mengajukan barang.');
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
}
