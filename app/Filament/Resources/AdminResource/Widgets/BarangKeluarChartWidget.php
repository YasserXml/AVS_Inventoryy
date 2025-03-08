<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Barangkeluar;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class BarangKeluarChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Barang keluar Chart';
    
    protected static string $color = 'warning';
    
    // Tambahkan method untuk mengecek visibility widget
    public static function canView(): bool
    {
        $user = request()->user();
        return $user && ($user->hasRole('super_admin') || $user->hasRole('administrator'));
    }
    
    protected function getData(): array
    {
        $barangkeluar = Barangkeluar::selectRaw('DATE_FORMAT(Tanggal_keluar_barang, "%Y-%m") as month, AVG(Jumlah_barang_keluar) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');
        
        return [
            'datasets' => [
                [
                    'label' => 'Data jumlah barang keluar',
                    'data' => $barangkeluar->values()->toArray(),
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
}