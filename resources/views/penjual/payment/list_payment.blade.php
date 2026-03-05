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
                                            {{ auth()->guard('customer')->check() ? auth()->guard('customer')->user()->customer_username : auth()->guard('penjual')->user()->penjual_username }}
                                        </h5>
                                        
                                        <p class="mb-0 text-sm text-gray-600">penjual</p>
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
                                    <h6 class="dropdown-header">Hello, {{ auth()->guard('customer')->check() ? auth()->guard('customer')->user()->customer_username : auth()->guard('penjual')->user()->penjual_username }}</h6>
                                </li>
                                    <hr class="dropdown-divider">
                                </li>
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
                <h3>Pembayaran</h3>
            </div>
            <section class="basic-choices">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                <h4 class="card-title">List Metode Pembayaran</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success mb-2 me-2" data-bs-toggle="modal"
                                            data-bs-target="#addModal">
                                            <i class="bi bi-plus-circle"></i> Tambah Pembayaran
                                        </button>
                                    </div>
                                    <table class="table table-striped table-responsive" id="table1">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Logo</th>
                                                <th>Nama Pembayaran</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($payments as $item)
                                                <tr>
                                                    <td>{{$loop->iteration }}</td>
                                                    <td>
                                                        @if($item->payment_image && file_exists(public_path('storage/' . $item->payment_image)))
                                                                <img src="{{ asset('storage/' . $item->payment_image) }}" alt="Product Image" width="100" class="img-thumbnail me-2">
                                                            @else
                                                                <span class="text-muted">Tidak ada gambar</span>
                                                        @endif
                                                    </td>
                                                    <td>{{$item->payment_name}}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                                        onclick="showEditModal({{ $item->payment_id }}, '{{ $item->payment_name }}', '{{ asset('storage/' . $item->payment_image) }}')">
                                                            <i class="bi bi-pencil-square me-2"></i> Edit
                                                        </button>
                                                        <button class="btn btn-outline-danger btn-sm" onclick="showDeletePaymentModal({{ $item->payment_id }}, '{{ $item->payment_name }}')">
                                                            <i class="bi bi-trash3 me-2"></i> Hapus
                                                        </button>                                                        
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
                {{-- Modal Tambah --}}
                <div class="modal fade text-left" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <form action="{{ route('payment.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addModalLabel">Tambah Metode Pembayaran</h5>
                                    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <h6 class="text-start">Nama Pembayaran:</h6>
                                    <div class="form-group">
                                        <input type="text" name="payment_name" class="form-control" placeholder="Nama Pembayran">
                                    </div>
                                    <h6 class="text-start">Logo</h6>
                                    <div class="form-group">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="file" name="payment_image" class="form-control" accept="image/*" onchange="previewImage(event)" style="max-width: 250px;">
                                            
                                            <img id="image-preview" src="#" alt="Preview" style="display: none; max-height: 80px; border-radius: 5px; border: 1px solid #ccc;">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success ms-1">Simpan</button>
                                </div>
                            </form> 
                        </div>
                    </div>
                </div>

                {{-- Modal Edit --}}
                <div class="modal fade text-left" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <form id="editForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Edit Pembayaran</h5>
                                    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="edit_id">
                                    <h6 class="text-start">Nama Pembayaran:</h6>
                                    <div class="form-group">
                                        <input type="text" name="payment_name" id="edit_name" class="form-control" placeholder="Nama Pembayaran">
                                    </div>
                                    <h6 class="text-start">Logo</h6>
                                    <div class="form-group">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="file" name="payment_image" class="form-control" accept="image/*" onchange="previewEditImage(event)" style="max-width: 250px;">
                                            
                                            <img id="edit-image-preview" src="#" alt="Preview" style="display: none; max-height: 80px; border-radius: 5px; border: 1px solid #ccc;">
                                        </div>
                                        <small class="text-danger mt-2">*Kosongkan jika tidak ingin mengubah gambar</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success ms-1">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>                
                
                {{-- Modal Hapus --}}
                <div class="modal fade text-left" id="deletePaymentModal" tabindex="-1" role="dialog" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <form id="deletePaymentForm" action="" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deletePaymentModalLabel">Hapus Pembayaran</h5>
                                    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus pembayaran <strong id="delete_payment_name"></strong>?</p>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Data yang dihapus tidak dapat dikembalikan!
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-danger ms-1">Hapus</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@push('scripts')
<script>
    // preview image input
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('image-preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    // preview image edit
    function previewEditImage(event) {
        const input = event.target;
        const preview = document.getElementById('edit-image-preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function showEditModal(id, name, imageUrl) {
        // Isi data ke modal
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;

        const preview = document.getElementById('edit-image-preview');
        preview.src = imageUrl;
        preview.style.display = 'block';

        // Ubah action form sesuai ID
        document.getElementById('editForm').action = `/payment/${id}`;

        // Tampilkan modal
        if (!editModalInstance) {
            editModalInstance = new bootstrap.Modal(document.getElementById('editModal'));
        }
        editModalInstance.show();
    }

    
    function showDeletePaymentModal(id, name) {
        const form = document.getElementById('deletePaymentForm');
        form.action = `/payment/${id}`; // pastikan route sesuai

        document.getElementById('delete_payment_name').textContent = name;
        const modal = new bootstrap.Modal(document.getElementById('deletePaymentModal'));
        modal.show();
    }

</script>
@endpush
@endsection

