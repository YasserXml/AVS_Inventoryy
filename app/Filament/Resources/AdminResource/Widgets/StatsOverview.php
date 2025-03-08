<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Barang;
use App\Models\Barangkeluar;
use App\Models\Barangmasuk;
use App\Models\Pengajuan;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    // Tambahkan polling untuk memperbarui widget secara otomatis
    protected static ?string $pollingInterval = '10s';

    protected function getPengajuanStatus(): string
    {
        $user = request()->user();
        
        // Gunakan query builder untuk menghitung status
        $statusCounts = Pengajuan::query()
            ->when(!$user->hasAnyRole(['super_admin', 'administrator']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Ambil jumlah untuk setiap status
        $pending = $statusCounts['pending'] ?? 0;
        $approved = $statusCounts['approved'] ?? 0;
        $rejected = $statusCounts['rejected'] ?? 0;

        return "⏳ Pending: {$pending}\n✅ Approved: {$approved}\n❌ Rejected: {$rejected}";
    }

    protected function getStats(): array
    {
        $user = request()->user();
        $stats = [];

        // Buat query dasar untuk pengajuan
        $pengajuanQuery = Pengajuan::query()
            ->when(!$user->hasAnyRole(['super_admin', 'administrator']), function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        // Data chart untuk 7 hari terakhir
        $chartData = collect(range(7, 1))
            ->map(function ($day) use ($pengajuanQuery) {
                return $pengajuanQuery->clone()
                    ->whereDate('created_at', now()->subDays($day - 1))
                    ->count();
            })
            ->toArray();

        // Stats Pengajuan
        $stats[] = Stat::make('Total Pengajuan', $pengajuanQuery->count())
            ->description($this->getPengajuanStatus())
            ->chart($chartData)
            ->color('info');

        // Stats Barang
        $stats[] = Stat::make('Total Barang', Barang::sum('Jumlah_barang'))
            ->description('Stok barang yang tersedia')
            ->descriptionIcon('heroicon-o-cube')
            ->icon('heroicon-o-cube')
            ->color('warning');

        // Stats khusus admin
        if ($user->hasAnyRole(['super_admin', 'administrator'])) {
            // Data chart barang masuk
            $chartDataMasuk = collect(range(7, 1))
                ->map(function ($day) {
                    return Barangmasuk::whereDate('created_at', now()->subDays($day - 1))
                        ->sum('Jumlah_barang');
                })
                ->toArray();

            $stats[] = Stat::make('Total Barang Masuk', Barangmasuk::sum('Jumlah_barang'))
                ->description('Total barang yang masuk')
                ->descriptionIcon('heroicon-o-arrow-down-circle')
                ->icon('heroicon-o-arrow-down-circle')
                ->chart($chartDataMasuk)
                ->color('success');

            // Data chart barang keluar
            $chartDataKeluar = collect(range(7, 1))
                ->map(function ($day) {
                    return Barangkeluar::whereDate('created_at', now()->subDays($day - 1))
                        ->sum('Jumlah_barang_keluar');
                })
                ->toArray();

            $stats[] = Stat::make('Total Barang Keluar', Barangkeluar::sum('Jumlah_barang_keluar'))
                ->description('Total barang yang keluar')
                ->descriptionIcon('heroicon-o-arrow-up-circle')
                ->icon('heroicon-o-arrow-up-circle')
                ->chart($chartDataKeluar)
                ->color('danger');
        }

        return $stats;
    }
}