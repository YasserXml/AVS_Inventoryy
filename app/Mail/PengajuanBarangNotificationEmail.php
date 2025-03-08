<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope; 
use Illuminate\Queue\SerializesModels;

class PengajuanBarangNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $pengajuan;
    public $submitter;  // property untuk menyimpan user yang membuat pengajuan

    public function __construct($pengajuan, $submitter)
    {
        $this->pengajuan = $pengajuan;
        $this->submitter = $submitter;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Barang Baru dari ' . $this->submitter->name,
        );
    }

    public function content(): Content
    {
        if (is_string($this->pengajuan->Tanggal_pengajuan)) {
            $this->pengajuan->Tanggal_pengajuan = \Carbon\Carbon::parse($this->pengajuan->Tanggal_pengajuan);
        }
        
        return new Content(
            markdown: 'email.pengajuan-notification',
            with: [
                'pengajuan' => $this->pengajuan,
                'user' => $this->submitter,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}