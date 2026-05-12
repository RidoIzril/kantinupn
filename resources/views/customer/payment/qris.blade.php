@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="bg-white shadow rounded-2xl p-6 w-full max-w-md">
        <h1 class="font-bold text-xl mb-4 text-center">Pembayaran QRIS</h1>
        
        <div class="text-center mb-4">
            {{-- WRAPPER supaya bisa di-hide setelah paid --}}
            <div id="qris-section">
                <a href="{{ $qris_url }}" target="_blank" class="block mb-2 text-blue-600 underline">Buka QRIS di Halaman Xendit</a>
                @if (!empty($qr_string))
                    {{-- Tampilkan QRCode asli dari string Xendit --}}
                    {!! QrCode::size(220)->generate($qr_string) !!}
                @else
                    {{-- Fallback jika belum ada qr_string --}}
                    <img 
                        src="/img/qris-default.png"
                        alt="QRIS" 
                        class="mx-auto border border-gray-300 rounded-lg"
                        style="width: 220px;">
                @endif
                <div class="mt-2 text-gray-600 text-sm">
                    Silakan scan kode QRIS ini dengan aplikasi e-wallet / mobile banking Anda.
                </div>
            </div>

            <span class="block font-bold text-lg mt-2">Total: Rp {{ number_format($total_harga, 0, ',', '.') }}</span>
        </div>
        
        <div id="status-payment" class="text-center my-3">
            <div class="font-medium text-yellow-600 animate-pulse">Menunggu pembayaran...</div>
        </div>

        <a href="{{ route('carts.cartcustomer', ['token' => $token]) }}" class="block text-center text-sm text-blue-500 mt-4 underline">Kembali ke Keranjang</a>
    </div>
</div>

<script>
    // Polling status pembayaran setiap 5 detik
    function checkStatus() {
        fetch("{{ route('payment.qris.checkstatus', ['order_id'=>$order_id]) }}")
            .then(res => res.json())
            .then(data => {
                if (data.status === 'paid') {

                    // HIDE link pembayaran dan QRIS
                    const qrisSection = document.getElementById('qris-section');
                    if (qrisSection) {
                        qrisSection.style.display = 'none';
                    }

                    document.getElementById('status-payment').innerHTML = `
                        <div class="text-green-600 font-bold">Pembayaran berhasil!</div>
                        <div class="mt-2">
                            <a href='{{ route('orders.history', ['token' => $token]) }}' class="underline text-blue-600">Lihat Riwayat Pesanan</a>
                        </div>
                    `;
                    clearInterval(window.checkQRInterval);

                } else if (data.status === 'expired' || data.status === 'failed' || data.status === 'canceled') {

                    document.getElementById('status-payment').innerHTML = `
                        <div class="text-red-600 font-bold">Pembayaran gagal atau kadaluarsa.</div>
                        <div class="mt-2">
                            <a href='{{ route('carts.cartcustomer', ['token' => $token]) }}' class="underline text-blue-600">Kembali ke Keranjang</a>
                        </div>
                    `;
                    clearInterval(window.checkQRInterval);
                }
            }).catch(err => {
                // Optional: Tampilkan error network jika ada
                console.error('Error polling status pembayaran:', err);
            });
    }
    window.checkQRInterval = setInterval(checkStatus, 5000);
</script>
@endsection