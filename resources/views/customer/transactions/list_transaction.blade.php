@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">

    {{-- SIDEBAR --}}
    @include('customer.sidebarcus')

    {{-- CONTENT --}}
    <div class="flex-1 p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-bold text-gray-800">Transaksi</h1>
        </div>

        {{-- CARD --}}
        <div class="bg-white rounded-xl shadow">

            <div class="px-6 py-4 border-b flex items-center gap-2">
                <span class="text-lg">🧾</span>
                <h2 class="font-semibold text-gray-700">List Transaksi</h2>
            </div>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b text-gray-500">
                        <tr>
                            <th class="text-left py-2">ID</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pengiriman</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @foreach($transactions as $trx)
                        <tr>
                            <td class="py-3 font-medium">
                                {{ $trx->custom_code_transaction }}
                            </td>

                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                            </td>

                            <td class="text-center font-semibold">
                                Rp {{ number_format($trx->order->total_price,0,',','.') }}
                            </td>

                            {{-- STATUS --}}
                            <td class="text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if($trx->status=='success') bg-green-100 text-green-700
                                    @elseif($trx->status=='failed') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ ucfirst($trx->status) }}
                                </span>
                            </td>

                            {{-- DELIVERY --}}
                            <td class="text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if($trx->delivery_status=='done') bg-green-100 text-green-700
                                    @elseif($trx->delivery_status=='delivered') bg-blue-100 text-blue-700
                                    @elseif($trx->delivery_status=='failed') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ ucfirst($trx->delivery_status) }}
                                </span>
                            </td>

                            {{-- ACTION --}}
                            <td class="text-center space-x-2">
                                <button
                                    onclick="openModal({{ $trx->transaction_id }})"
                                    class="px-3 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                                    Detail
                                </button>

                                @if($trx->delivery_status=='delivered')
                                <button
                                    onclick="acceptOrder({{ $trx->transaction_id }})"
                                    class="px-3 py-1 rounded bg-green-600 text-white text-xs hover:bg-green-700">
                                    Terima
                                </button>
                                @endif

                                @if(!in_array($trx->status,['failed','success']))
                                <button
                                    onclick="cancelOrder({{ $trx->transaction_id }})"
                                    class="px-3 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700">
                                    Batalkan
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL DETAIL --}}
        @foreach($transactions as $trx)
        <div id="modal-{{ $trx->transaction_id }}"
             class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

            <div class="bg-white w-full max-w-3xl rounded-xl shadow-lg p-6 relative">

                <button onclick="closeModal({{ $trx->transaction_id }})"
                        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                    ✕
                </button>

                <h3 class="text-lg font-semibold mb-4">
                    Detail Transaksi
                </h3>

                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <p><strong>Nama:</strong> {{ $trx->order->customers->customer_fullname }}</p>
                        <p><strong>Telepon:</strong> {{ $trx->order->customers->customer_contact }}</p>
                        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p><strong>Status:</strong> {{ ucfirst($trx->status) }}</p>
                        <p><strong>Pengiriman:</strong> {{ ucfirst($trx->delivery_status) }}</p>
                        <p><strong>Pembayaran:</strong> Bank {{ $trx->payment->payment_name }}</p>
                    </div>
                </div>

                <hr class="my-3">

                <h4 class="font-semibold mb-2">Produk</h4>

                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left">Produk</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($trx->order->items as $item)
                        <tr>
                            <td class="py-2">
                                {{ $item->product->product_name ?? '-' }}
                            </td>
                            <td class="text-center">
                                Rp {{ number_format($item->price_per_unit,0,',','.') }}
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center font-semibold">
                                Rp {{ number_format($item->subtotal,0,',','.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-right font-bold mt-4">
                    Total: Rp {{ number_format($trx->order->total_price,0,',','.') }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- SCRIPT --}}
<script>
function openModal(id){
    document.getElementById('modal-'+id).classList.remove('hidden')
    document.getElementById('modal-'+id).classList.add('flex')
}
function closeModal(id){
    document.getElementById('modal-'+id).classList.add('hidden')
}
</script>
@endsection
