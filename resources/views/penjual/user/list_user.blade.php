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
                                <h4 class="card-title">List Customer</h4>
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <table class="table table-striped table-responsive" id="table1">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Username</th>
                                                <th class="text-nowrap">Full Name</th>
                                                <th>Email</th>
                                                <th>DOB</th>
                                                <th>Alamat</th>
                                                <th>Kota</th>
                                                <th>No Telepon</th>
                                                <th>No. Paypal</th>

                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($customers as $item)
                                                <tr class="text-nowrap">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->customer_username }}</td>
                                                    <td>{{ $item->customer_fullname }}</td>
                                                    <td>{{ $item->customer_email }}</td>
                                                    <td>{{ $item->customer_dob }}</td>
                                                    <td>{{ $item->customer_address }}</td>
                                                    <td>{{ $item->customer_city }}</td>
                                                    <td>{{ $item->customer_contact }}</td>
                                                    <td>{{ $item->customer_paypal }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="showEditModal(
                                                            {{ $item->customer_id }},
                                                            '{{ $item->customer_username }}',
                                                            '{{ $item->customer_fullname }}',
                                                            '{{ $item->customer_email }}',
                                                            '{{ $item->customer_dob }}',
                                                            '{{ $item->customer_address }}',
                                                            '{{ $item->customer_city }}',
                                                            '{{ $item->customer_contact }}',
                                                            '{{ $item->customer_paypal }}'
                                                        )" data-bs-toggle="modal" data-bs-target="#editModal">
                                                            <i class="bi bi-pencil-square me-2"></i> Edit
                                                        </button>
                                                        <button class="btn btn-outline-danger btn-sm" onclick="showDeletePaymentModal(
                                                            {{ $item->customer_id }},
                                                            '{{ $item->customer_username }}'
                                                        )">
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
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                        <form id="editForm" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body row">
                                    <input type="hidden" id="editId" name="id">
                                    <div class="col-md-6 mb-2">
                                        <label>Username</label>
                                        <input type="text" id="editUsername" name="customer_username" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>Full Name</label>
                                        <input type="text" id="editFullname" name="customer_fullname" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>Email</label>
                                        <input type="email" id="editEmail" name="customer_email" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>DOB</label>
                                        <input type="date" id="editDob" name="customer_dob" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>Alamat</label>
                                        <input type="text" id="editAddress" name="customer_address" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>Kota</label>
                                        <input type="text" id="editCity" name="customer_city" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>No Telepon</label>
                                        <input type="text" id="editContact" name="customer_contact" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label>No. Paypal</label>
                                        <input type="text" id="editPaypal" name="customer_paypal" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Update</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                    
                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                        <form id="deleteForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Apakah Anda yakin ingin menghapus customer <strong id="deleteName"></strong>?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                </div>
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
    function showEditModal(id, username, fullname, email, dob, address, city, contact, paypal) {
        // mengisi modal berdasarkan ID
        document.getElementById('editId').value = id;
        // Form data
        document.getElementById('editUsername').value = username;
        document.getElementById('editFullname').value = fullname;
        document.getElementById('editEmail').value = email;
        document.getElementById('editDob').value = dob;
        document.getElementById('editAddress').value = address;
        document.getElementById('editCity').value = city;
        document.getElementById('editContact').value = contact;
        document.getElementById('editPaypal').value = paypal;
        // Aksi edit route
        document.getElementById('editForm').action = '/user/' + id;
        let editModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editModal'));
        editModal.show();
    }

    function showDeletePaymentModal(id, name) {
        document.getElementById('deleteName').textContent = name;
        document.getElementById('deleteForm').action = '/user/' + id;
        let deleteModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>

@endpush
@endsection

