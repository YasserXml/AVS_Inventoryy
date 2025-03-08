<?php

namespace App\Observers;

use App\Jobs\SendNotificationJob;
use App\Jobs\SendPengajuanNotification;
use App\Models\Pengajuan;
use App\Models\User;
use App\Notifications\PengajuanNotification;
use App\Notifications\Pengajuanotification;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Str;

class PengajuanObserver
{
    /**
     * Handle the Pengajuan "created" event.
     */
    public function created(Pengajuan $pengajuan): void
    {
        try {
            // Notifikasi ke admin ketika ada pengajuan baru
            $admins = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super_admin', 'administrator']);
            })->get();

            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Ada Pengajuan Baru')
                    ->info()
                    ->body("📝 Pengajuan Baru\n" .
                        "👤 Pengaju: {$pengajuan->user->name}\n" .
                        "📦 Barang: {$pengajuan->barangs->Nama_barang}\n" .
                        "⏰ Waktu: " . Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'))
                    ->icon('heroicon-o-plus-circle')
                    ->sendToDatabase($admin);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Observer Error: ' . $e->getMessage(), [
                'pengajuan_id' => $pengajuan->id
            ]);
        }
    }

    /**
     * Handle the Pengajuan "updated" event.
     */
    public function updated(Pengajuan $pengajuan): void
    {
        try {
            if ($pengajuan->isDirty('status')) {
                if ($pengajuan->status === 'approved') {
                    // Notifikasi untuk user ketika pengajuan disetujui    
                    Notification::make()
                        ->title('Pengajuan anda disetujui')
                        ->success()
                        ->body("✅ Status: Disetujui\n" .
                              "📦 Barang: {$pengajuan->barangs->Nama_barang}\n" .
                              "⏰ Waktu: " . Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'))
                        ->icon('heroicon-o-document-check')
                        ->sendToDatabase($pengajuan->user);
                } elseif ($pengajuan->status === 'rejected') {
                    // Notifikasi untuk user ketika pengajuan ditolak
                    Notification::make()
                        ->title('Pengajuan Ditolak')
                        ->danger()
                        ->body("❌ Status: Ditolak\n" .
                              "📦 Barang: {$pengajuan->barangs->Nama_barang}\n" .
                              "📝 Alasan: {$pengajuan->reject_reason}\n" .
                              "⏰ Waktu: " . Carbon::now('Asia/Jakarta')->format('d/m/Y H:i'))
                        ->icon('heroicon-o-x-circle')
                        ->sendToDatabase($pengajuan->user);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Observer Error: ' . $e->getMessage(), [
                'pengajuan_id' => $pengajuan->id
            ]);
        }
    }

    /**
     * Handle the Pengajuan "deleted" event.
     */
    public function deleted(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "restored" event.
     */
    public function restored(Pengajuan $pengajuan): void
    {
        //
    }

    /**
     * Handle the Pengajuan "force deleted" event.
     */
    public function forceDeleted(Pengajuan $pengajuan): void
    {
        //
    }
}
