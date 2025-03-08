@component('mail::message')
<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ asset('icon.png') }}" alt="Inventory AVS Logo" style="max-width: 100px; height: auto;">
    <h1 style="margin-top: 10px;">Inventory AVS</h1>
</div>

#  Pengajuan Barang Baru

@component('mail::panel')
Pengajuan dari: **{{ $pengajuan->user->name }}**  
Tanggal: **{{ \Carbon\Carbon::parse($pengajuan->Tanggal_pengajuan)->format('d F Y') }}**
@endcomponent

## Detail Barang
@component('mail::table')
| Informasi | Detail |
|:----------|:-------|
| Nama Barang: | {{ $pengajuan->barangs->Nama_barang }} |
| Kategori: | {{ $pengajuan->kategoris->Kategori_barang }} |
| Jenis: | {{ $pengajuan->jenis->Jenis_barang }} |
| Jumlah Diajukan: | {{ $pengajuan->Jumlah_barang_diajukan }} unit |
@endcomponent

##  Keterangan
@component('mail::panel')
{{ $pengajuan->keterangan }}
@endcomponent

@component('mail::button', ['url' => url('/admin/pengajuan'), 'color' => 'primary'])
Review Pengajuan
@endcomponent

Terima kasih,<br>
{{ $user->name }}

---
<small>
Ini adalah email otomatis. Mohon tidak membalas email ini.  
Untuk meninjau pengajuan, silakan klik tombol di atas.
</small>
@endcomponent