@extends('layouts.app')
@section('title','Tambah Profile Penjual')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-xl font-bold mb-4">Tambah Profile Penjual</h1>

    <form action="{{ route('penjual.profile.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-5 rounded shadow">
        @csrf
        <input name="users_id" value="{{ old('users_id') }}" placeholder="users_id" class="w-full border p-2 rounded" required>
        <input name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Nama Lengkap" class="w-full border p-2 rounded" required>
        <input name="kontak" value="{{ old('kontak') }}" placeholder="Kontak" class="w-full border p-2 rounded">
        <input name="gender" value="{{ old('gender') }}" placeholder="Gender" class="w-full border p-2 rounded">
        <select name="status" class="w-full border p-2 rounded" required>
            <option value="aktif">aktif</option>
            <option value="nonaktif">nonaktif</option>
        </select>

        <hr>
        <input name="tenant_name" value="{{ old('tenant_name') }}" placeholder="Nama Tenant" class="w-full border p-2 rounded" required>
        <input name="no_tenant" value="{{ old('no_tenant') }}" placeholder="No Tenant" class="w-full border p-2 rounded">
        <input type="file" name="foto_tenant" class="w-full border p-2 rounded">

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
            <a href="{{ route('penjual.profile.index') }}" class="px-4 py-2 bg-slate-300 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection