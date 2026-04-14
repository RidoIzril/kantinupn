@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto mt-10 bg-white rounded shadow p-6">
    <h2 class="text-lg mb-4 font-bold">Pembayaran QRIS</h2>
    <div class="flex flex-col items-center">
        <img src="{{ $qr_url }}" alt="QRIS" class="w-48 h-48 border mb-3"/>
        <p>Silakan scan QRIS di atas via aplikasi e-wallet pilihan Anda.</p>
        <b class="mt-2">Nominal: Rp {{ number_format($amount, 0, ',', '.') }}</b>
        <p class="mt-4">Order ID: {{ $order_id }}</p>
        <div id="status-area" class="mt-4 text-sm text-gray-500">Menunggu pembayaran...</div>
    </div>
</div>
<script>
let qrId = @json($qr_id);
const statusArea = document.getElementById('status-area');
let polling = setInterval(function() {
    fetch('/payment/status_qris?qr_id=' + qrId)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'COMPLETED') {
                statusArea.innerHTML = "<b class='text-green-600'>Pembayaran Sukses!</b>";
                clearInterval(polling);
            } else if(data.status === 'INACTIVE') {
                statusArea.innerHTML = "<b class='text-red-600'>Kode QR tidak aktif</b>";
                clearInterval(polling);
            }
        });
}, 4000);
</script>
@endsection