<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $transaction->custom_code_transaction }}</title>
</head>
<body>
    <h2>Halo {{ $transaction->order->customers->customer_fullname }},</h2>
    <p>Terima kasih telah berbelanja di APOTEKNih BOS. Berikut kami lampirkan invoice untuk transaksi Anda:</p>
    
    <p><strong>Nomor Invoice:</strong> {{ $transaction->custom_code_transaction }}</p>
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</p>
    <p><strong>Total:</strong> Rp {{ number_format($transaction->order->total_price, 0, ',', '.') }}</p>
    <p><strong>Berikut Invoice Dalam Bentuk .PDF</strong></p>
    
    
    <p>Salam,<br>
    APOTEKNih BOS</p>
</body>
</html>