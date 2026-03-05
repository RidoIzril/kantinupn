@extends('layouts.app')

@section('content')
<div id="app">
    @include('penjual.sidebarpenjual')
    <div id="main" class='layout-navbar navbar-fixed'>
        <header>
            <nav class="navbar navbar-expand navbar-light navbar-top">
                <div class="container-fluid">
                    <a href="#" class="burger-btn d-block d-xl-none">
                        <i class="bi bi-justify fs-3"></i>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto mb-lg-0">
                        </ul>
                        <div class="dropdown">
                            <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-menu d-flex">
                                    <div class="user-name text-end me-3">
                                        <h5 class="mb-0 text-primary-600">
                                            {{ auth()->guard('penjual')->user()->penjual_username }}
                                        </h5>
                                        <p class="mb-0 text-sm text-gray-600">Penjual</p>
                                    </div>
                                    <div class="user-img d-flex align-items-center">
                                        <div class="avatar avatar-md">
                                            <img src="{{ asset('template/dist/assets/compiled/jpg/1.jpg')}}">
                                        </div>
                                    </div>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="min-width: 11rem;">
                                <li>
                                    <h6 class="dropdown-header">Hello, {{ auth()->guard('penjual')->user()->penjual_username }}</h6>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="icon-mid bi bi-box-arrow-left me-2"></i> Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        <div id="main-content">
            <div class="page-heading">
                <h3>Transaksi</h3>
            </div>
            <div class="page-heading">
                <div class="section">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <h4 class="card-title">Kelola Transaksi</h4>
                                </div>                        
                                <div class="card-body">
                                    <table class="table table-striped table-responsive" id="table1">
                                        <thead>
                                            <tr>
                                                <th>Kode Transaksi</th>
                                                <th>Pelanggan</th>
                                                <th>Tanggal</th>
                                                <th>Total</th>
                                                <th>Status Pesanan</th>
                                                <th>Pengiriman</th>
                                                <th>Aksi</th>
                                                <th class="text-center">Status Akhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->custom_code_transaction }}</td>
                                                <td>{{ $transaction->order->customers->customer_fullname }}</td>
                                                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</td>
                                                <td>Rp {{ number_format($transaction->order->total_price, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($transaction->status == 'success') bg-success 
                                                        @elseif($transaction->status == 'failed') bg-danger 
                                                        @elseif($transaction->status == 'pending') bg-info
                                                        @else bg-warning @endif">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge 
                                                        @if($transaction->delivery_status == 'delivered') bg-primary
                                                        @elseif($transaction->delivery_status == 'failed') bg-danger 
                                                        @elseif($transaction->delivery_status == 'done') bg-success
                                                        @else bg-info @endif">
                                                        {{ ucfirst($transaction->delivery_status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle me-1" type="button" id="dropdownMenuButton{{ $transaction->transaction_id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-eye me-2"></i>Aksi
                                                        </button>
                                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $transaction->transaction_id }}">
                                                            @if($transaction->status == 'pending')
                                                                <li>
                                                                    <a class="dropdown-item update-status" href="javascript:void(0);" 
                                                                        data-id="{{ $transaction->transaction_id }}" 
                                                                        data-status="processing">
                                                                        <i class="bi bi-check-circle me-2"></i> Proses Pesanan
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item cancel-order" href="javascript:void(0);" 
                                                                        data-id="{{ $transaction->transaction_id }}">
                                                                        <i class="bi bi-x-circle me-2"></i> Batalkan
                                                                    </a>
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#transactionDetailModal{{ $transaction->transaction_id }}">
                                                                    <i class="bi bi-eye me-2"></i> Lihat Detail
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>                                                
                                                <td class="text-center">
                                                    @if($transaction->delivery_status == 'done')
                                                        <button class="btn btn-sm btn-success">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                    @endif
                                                    @if($transaction->status == 'failed')
                                                        <button class="btn btn-sm btn-danger">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Detail Transaksi -->
@foreach ($transactions as $transaction)
<div class="modal fade text-left" id="transactionDetailModal{{ $transaction->transaction_id }}" tabindex="-1"
    role="dialog" aria-labelledby="transactionDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailModalLabel">Detail Transaksi</h5>
                <button type="button" class="close rounded-pill" data-bs-dismiss="modal"
                    aria-label="Close">x
                </button>
            </div>
            <div class="modal-body">
                <div class="invoice">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ $transaction->custom_code_transaction }}</h6>
                        {{-- status --}}
                        <span class="badge 
                            @if($transaction->status == 'success') bg-success 
                            @elseif($transaction->status == 'failed') bg-danger 
                            @elseif($transaction->status == 'pending') bg-info
                            @else bg-warning @endif">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Nama:</strong> {{ $transaction->order->customers->customer_fullname }}</p>
                            <p class="mb-1"><strong>Telepon:</strong> {{ $transaction->order->customers->customer_contact }}</p>
                            <p class="mb-1"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Status Pengiriman:</strong> 
                                <span class="badge 
                                    @if($transaction->delivery_status == 'delivered') bg-primary
                                    @elseif($transaction->delivery_status == 'failed') bg-danger 
                                    @elseif($transaction->delivery_status == 'done') bg-success
                                    @else bg-info @endif">
                                    {{ ucfirst($transaction->delivery_status) }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Metode Pembayaran:</strong>
                                <img src="{{ asset('storage/') }}/{{ $transaction->payment->payment_image }}" 
                                    width="40" class="me-2">Bank {{ $transaction->payment->payment_name }}
                                @if($transaction->status == 'failed') <span class="text-success">(Refund)</span> @endif
                            </p>
                            <p class="mb-1"><strong>Alamat Pengiriman:</strong> {{ $transaction->delivery_address }}</p>
                        </div>
                    </div>
                    <hr class="my-3">
                    <h6 class="mb-3">Produk</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Jumlah</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- loop menampilkan produk --}}
                                @forelse ($transaction->order->items as $item)
                                    <tr>
                                        <td>{{ $item->product ? $item->product->product_code : 'Kode tidak tersedia' }}</td>
                                        <td>{{ $item->product ? $item->product->product_name : '-' }}</td>
                                        <td>Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada produk</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td>Rp {{ number_format($transaction->order->total_price, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    $(document).ready(function() {
        $('.update-status').click(function() {
            // Dapatkan ID transaksi
            const transactionId = $(this).data('id');

            // SweetAlert2 Konfirmasi
            confirmAction({
                title: 'Proses Pesanan?',
                text: "Pesanan akan diproses dan dikirim. Apakah Anda yakin ingin melanjutkan?",
                icon: 'question',
                confirmText: 'Ya, proses!',
                cancelText: 'Batal'
            }, function() {
                // Jika dikonfirmasi, AJAX ke server
                $.ajax({
                    url: `/penjual/kelola_transaksi/update/${transactionId}`,
                    type: 'POST',
                    data: {
                        //csdf token laravel
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        //respons success
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Pesanan berhasil diproses!'
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            //respons error
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan.'
                            });
                        }
                    },
                });
            });
        });

        // --- Script Batalkan Transaksi
        $('.cancel-order').click(function() {
            const transactionId = $(this).data('id');

            confirmAction({
                title: 'Batalkan Pesanan?',
                text: "Anda yakin membatalkan pesanan ini? pesanan tidak dapat diproses kembali.",
                icon: 'warning',
                confirmText: 'Ya, batalkan!',
                cancelText: 'Batal'
            }, function() {
                $.ajax({
                    url: `/penjual/kelola_transaksi/cancel/${transactionId}`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Pesanan berhasil dibatalkan!'
                            });
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Terjadi kesalahan.'
                            });
                        }
                    },
                });
            });
        });
    });
</script>
@endpush
@endsection