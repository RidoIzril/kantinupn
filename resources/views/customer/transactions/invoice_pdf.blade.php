<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->custom_code_transaction }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        .footer { margin-top: 50px; text-align: right; }
        .signature { width: 200px; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <img src="{{ public_path('template/dist/assets/compiled/png/logoapotekbos.png') }}" class="logo" alt="Logo">
        <h2>APOTEKNih BOS</h2>
        <p>Surabaya Timur | 123456789 | apoteknihbos@gmail.com</p>
    </div>

    <hr>

    {{-- Info Invoice --}}
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <p>Nama: {{ $transaction->order->customers->customer_fullname }}</p>
                <p>Telepon: {{ $transaction->order->customers->customer_contact }}</p>
                <p>Tanggal: {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</p>
            </td>
            <td style="width: 50%; vertical-align: top;">
                <p>Status: {{ $transaction->status }}</p>
                <p>Metode Pembayaran: {{ $transaction->payment->payment_name }}</p>
                <p>Alamat Pengiriman: {{ $transaction->delivery_address }}</p>
            </td>
        </tr>
    </table>    

    {{-- Tabel Produk --}}
    <h4>Produk</h4>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->order->items as $item)
            <tr>
                <td>{{ $item->product->product_name }}</td>
                <td>Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" align="right"><strong>Total:</strong></td>
                <td><strong>Rp {{ number_format($transaction->order->total_price, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer tanda tangan --}}
    <div class="footer">
        <p>Hormat Kami,</p>
        <img src="{{ public_path('template/dist/assets/compiled/png/ttdapk.png') }}" class="logo" alt="Logo">
        <p>APOTEKNih BOS</p>
    </div>

</body>
</html>
