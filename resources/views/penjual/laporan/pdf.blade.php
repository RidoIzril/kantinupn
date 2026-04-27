<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; }
        th { background: #eee; }
    </style>
</head>
<body>

    <h2>Laporan Penjualan</h2>

    <p>
        Periode:
        {{ $request->start_date ?? '-' }} s/d {{ $request->end_date ?? '-' }}
    </p>

    <p>Total Pendapatan: Rp {{ number_format($totalPendapatan,0,',','.') }}</p>
    <p>Total Transaksi: {{ $totalTransaksi }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Customer</th>
                <th>Tanggal</th>
                <th>Metode</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksis as $t)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $t->order->customer->nama_lengkap ?? '-' }}</td>
                <td>{{ $t->waktu_bayar }}</td>
                <td>{{ strtoupper($t->metode_pembayaran) }}</td>
                <td>Rp {{ number_format($t->jumlah_bayar,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>