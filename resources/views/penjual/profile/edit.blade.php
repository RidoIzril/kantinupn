@extends('layouts.app')
@section('title','Edit Profile Penjual')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-xl font-bold mb-4">Edit Profile Penjual</h1>

    <form action="{{ route('penjual.profile.update') }}" method="POST" enctype="multipart/form-data"
          class="space-y-4 bg-white p-5 rounded shadow">
        @csrf
        @method('PUT')

        {{-- users_id jangan bisa diedit --}}
        <input type="hidden" name="users_id" value="{{ $penjual->users_id }}">

        <input name="nama_lengkap"
               value="{{ old('nama_lengkap', $penjual->nama_lengkap) }}"
               placeholder="Nama Lengkap"
               class="w-full border p-2 rounded" required>

        <input name="kontak"
               value="{{ old('kontak', $penjual->kontak) }}"
               placeholder="Kontak"
               class="w-full border p-2 rounded">

        <input name="gender"
               value="{{ old('gender', $penjual->gender) }}"
               placeholder="Gender"
               class="w-full border p-2 rounded">

        <select name="status" class="w-full border p-2 rounded" required>
            <option value="aktif" {{ old('status', $penjual->status)=='aktif'?'selected':'' }}>aktif</option>
            <option value="nonaktif" {{ old('status', $penjual->status)=='nonaktif'?'selected':'' }}>nonaktif</option>
        </select>

        <hr>

        <input name="tenant_name"
               value="{{ old('tenant_name', $penjual->tenant->tenant_name ?? '') }}"
               placeholder="Nama Tenant"
               class="w-full border p-2 rounded" required>

        <input name="no_tenant"
               value="{{ old('no_tenant', $penjual->tenant->no_tenant ?? '') }}"
               placeholder="No Tenant"
               class="w-full border p-2 rounded">

        <input type="file" name="foto_tenant" class="w-full border p-2 rounded">

        @if(!empty($penjual->tenant->foto_tenant))
            <img src="{{ asset('storage/'.$penjual->tenant->foto_tenant) }}"
                 class="w-24 h-24 object-cover rounded">
        @endif

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
            <a href="{{ route('penjual.profile.show') }}" class="px-4 py-2 bg-slate-300 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection