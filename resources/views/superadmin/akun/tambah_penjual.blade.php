@extends('layouts.app')
@section('title','Tambah Penjual')

@section('content')
@include('superadmin.sidebarsuperadmin')
<h1 class="text-xl font-bold mb-6">Tambah Akun Penjual</h1>

{{-- ERROR --}}
@if ($errors->any())
<div class="bg-red-100 text-red-700 p-3 rounded mb-4">
    <ul class="list-disc ml-5">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ route('superadmin.penjual.store') }}"
      enctype="multipart/form-data"
      class="space-y-4 max-w-xl">

    @csrf

    {{-- NAMA LENGKAP --}}
    <input name="username"
           class="w-full border p-2 rounded"
           placeholder="username penjual"
           required>

    <input type="password" name="password" placeholder="Password" required
            class="w-full px-4 py-2 rounded">

    {{-- NAMA TENANT --}}
    <input name="nama_lengkap"
           class="w-full border p-2 rounded"
           placeholder="Nama Tenant"
           required>

    {{-- NO HP --}}
    <input name="penjual_nohp"
           class="w-full border p-2 rounded"
           placeholder="No HP"
           required>

    {{-- GENDER --}}
    <select name="penjual_gender"
            class="w-full border p-2 rounded"
            required>
        <option value="">-- Pilih Gender --</option>
        <option value="Laki-laki">Laki-laki</option>
        <option value="Perempuan">Perempuan</option>
    </select>

    {{-- STATUS --}}
    <select name="penjual_status"
            class="w-full border p-2 rounded">
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
    </select>

    {{-- USERNAME --}}
    <input name="penjual_username"
           class="w-full border p-2 rounded"
           placeholder="Username"
           required>

    {{-- PASSWORD --}}
    <input type="password"
           name="penjual_password"
           class="w-full border p-2 rounded"
           placeholder="Password"
           required>

    {{-- FOTO TENANT --}}
    <div>
        <label class="block text-sm font-medium mb-1">
            Foto Tenant
        </label>
        <input type="file"
               name="foto_tenant"
               class="w-full border p-2 rounded">
        <p class="text-xs text-gray-500 mt-1">
            Upload foto kios / tenant (jpg, png, max 2MB)
        </p>
    </div>

    <div class="flex gap-3 pt-2">
        <button class="bg-green-600 text-white px-4 py-2 rounded">
            Simpan
        </button>
        <a href="{{ route('superadmin.penjual.index') }}"
           class="bg-gray-300 px-4 py-2 rounded">
            Kembali
        </a>
    </div>
</form>
@endsection
