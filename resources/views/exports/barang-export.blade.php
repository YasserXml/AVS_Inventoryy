<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Serial Number</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Kategori Barang</th>
            <th>Jenis Barang</th>
            <th>Jumlah Barang</th>
            <th>Harga Barang</th>
        </tr>
    </thead>
    <tbody>
        @foreach($barangs as $barang)
        <tr>
            <td>{{ $barang->id }}</td>
            <td>{{ $barang->Serial_number }}</td>
            <td>{{ $barang->Kode_barang }}</td>
            <td>{{ $barang->Nama_barang }}</td>
            <td>{{ $barang->Kategori_barang ?? '(Tidak ada)' }}</td>
            <td>{{ $barang->Jenis_barang ?? '(Tidak ada)' }}</td>
            <td>{{ $barang->Jumlah_barang }}</td>
            <td>{{ $barang->Harga_barang }}</td>
        </tr>
        @endforeach
        
        <!-- Baris kosong sebelum total -->
        <tr>
            <td colspan="8"></td>
        </tr>
        
        <!-- Baris total -->
        <tr>
            <td colspan="5"></td>
            <td>TOTAL:</td>
            <td>{{ $totalJumlah }}</td>
            <td>{{ $totalHarga }}</td>
        </tr>
    </tbody>
</table>