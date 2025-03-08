<?php

namespace App\Filament\Resources\PengajuanResource\Pages;

use App\Filament\Resources\PengajuanResource;
use App\Mail\PengajuanBarangNotificationEmail;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;

class CreatePengajuan extends CreateRecord
{
    protected static string $resource = PengajuanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return "Buat pengajuan";
    }

    protected function getCreatedNotification(): ?Notification
    {
    return Notification::make()
        ->success()
        ->title('Pengajuan berhasil dibuat')
        ->body('Mohon tunggu informasi selanjutnya')
        ->seconds(5);
    }

    protected function afterCreate(): void
{
    $pengajuan = $this->record->load(['user', 'kategoris', 'jenis']); // Load semua relasi yang dibutuhkan
    $submitter = Filament::auth()->user(); // Ambil user yang sedang membuat pengajuan
    
    $adminEmails = User::whereHas('roles', fn ($query) => 
        $query->whereIn('name', ['super_admin', 'administrator'])
    )->pluck('email')->toArray();

    foreach ($adminEmails as $email) {
        Mail::to($email)->queue(new PengajuanBarangNotificationEmail($pengajuan, $submitter));
    }
}
}
