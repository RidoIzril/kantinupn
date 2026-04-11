@extends('layouts.app')
@section('title','Edit Profile Penjual')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-xl font-bold mb-4">Edit Profile Penjual</h1>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-700">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('penjual.profile.update', ['token' => request('token')]) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-4 bg-white p-5 rounded shadow">
        @csrf
        @method('PUT')

        <input type="hidden" name="users_id" value="{{ $penjual->users_id }}">
        <input type="hidden" name="token" value="{{ request('token') }}">

        <input name="username"
               value="{{ old('username', $user->username ?? '') }}"
               placeholder="Username"
               class="w-full border p-2 rounded" required>

        <input name="nama_lengkap"
               value="{{ old('nama_lengkap', $penjual->nama_lengkap) }}"
               placeholder="Nama Lengkap"
               class="w-full border p-2 rounded" required>

        <input name="kontak"
               value="{{ old('kontak', $penjual->kontak) }}"
               placeholder="Kontak"
               class="w-full border p-2 rounded">

        <select name="gender" class="w-full border p-2 rounded">
            <option value="">-- Pilih Gender --</option>
            <option value="Laki-Laki" {{ old('gender', $penjual->gender) == 'Laki-Laki' ? 'selected' : '' }}>Laki-Laki</option>
            <option value="Perempuan" {{ old('gender', $penjual->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
        </select>

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

        <hr>

        <h2 class="font-semibold">Ganti Password (Opsional)</h2>
        <input type="password" name="current_password" placeholder="Password Saat Ini" class="w-full border p-2 rounded">
        <input type="password" name="new_password" placeholder="Password Baru" class="w-full border p-2 rounded">
        <input type="password" name="new_password_confirmation" placeholder="Konfirmasi Password Baru" class="w-full border p-2 rounded">

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
            <a href="{{ route('penjual.profile.show', ['token' => request('token')]) }}" class="px-4 py-2 bg-slate-300 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection