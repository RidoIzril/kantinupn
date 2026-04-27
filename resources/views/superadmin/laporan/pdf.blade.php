<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>

    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .info {
            text-align: center;
            margin-bottom: 15px;
        }

        .info p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid black;
        }

        th {
            background: #eee;
        }

        th, td {
            padding: 6px;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

    <!-- JUDUL -->
    <h2>LAPORAN PENJUALAN</h2>

    <!-- INFO -->
    <div class="info">
        <p><b>Periode:</b> {{ $request->start_date ?? '-' }} s/d {{ $request->end_date ?? '-' }}</p>
        <p><b>Tenant:</b> {{ $tenantNama }}</p>
    </div>

    <!-- SUMMARY -->
    <p><b>Total Pendapatan:</b> Rp {{ number_format($totalPendapatan,0,',','.') }}</p>
    <p><b>Total Transaksi:</b> {{ $totalTransaksi }}</p>

    <!-- TABEL -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tenant</th>
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

                <!-- TENANT -->
                <td>
                    @php
                        $tenants = collect($t->order->details ?? [])
                            ->map(fn($d) => $d->produk->tenant->tenant_name ?? null)
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp

                    {{ $tenants->isNotEmpty() ? $tenants->implode(', ') : '-' }}
                </td>

                <!-- CUSTOMER -->
                <td>{{ $t->order->customer->nama_lengkap ?? '-' }}</td>

                <!-- TANGGAL -->
                <td>
                    {{ $t->order->order_tanggal 
                        ? \Carbon\Carbon::parse($t->order->order_tanggal)->format('d-m-Y H:i')
                        : '-' }}
                </td>

                <!-- METODE -->
                <td>{{ strtoupper($t->metode_pembayaran) }}</td>

                <!-- TOTAL -->
                <td class="text-right">
                    Rp {{ number_format($t->jumlah_bayar,0,',','.') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>