@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-slate-100">
    @include('customer.sidebarcus')

    <div class="flex-1 p-6 md:p-10">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="px-8 py-5 border-b border-slate-200">
                <h1 class="text-xl font-bold text-slate-800">Profile Customer</h1>
                <p class="text-sm text-slate-500 mt-1">Lengkapi data profil kamu</p>
            </div>

            {{-- FORM UPDATE BIODATA --}}
            <form method="POST"
                  action="{{ route('profile.profilecustomer.update', ['token' => request('token')]) }}"
                  class="px-8 py-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="token" value="{{ request('token') }}">

                @if(session('success'))
                    <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any() && !session('success_password'))
                    <div class="bg-red-100 text-red-700 px-4 py-3 rounded-lg text-sm">
                        <ul class="list-disc ml-5">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" value="{{ $user->username }}" disabled
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-100 text-slate-600">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $customer->nama_lengkap) }}"
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $customer->tanggal_lahir) }}"
                               class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                            <option value="">Pilih</option>
                            <option value="laki-laki" {{ old('jenis_kelamin', $customer->jenis_kelamin) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="perempuan" {{ old('jenis_kelamin', $customer->jenis_kelamin) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fakultas</label>
                        <input type="text" name="fakultas" value="{{ old('fakultas', $customer->fakultas) }}"
                               class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                        <select name="status" class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                            <option value="">Pilih</option>
                            <option value="Mahasiswa" {{ old('status', $customer->status) == 'Mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            <option value="Dosen" {{ old('status', $customer->status) == 'Dosen' ? 'selected' : '' }}>Dosen</option>
                            <option value="Tendik" {{ old('status', $customer->status) == 'Tendik' ? 'selected' : '' }}>Tendik</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kontak</label>
                    <input type="text" name="kontak" value="{{ old('kontak', $customer->kontak) }}"
                           class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700 transition font-semibold text-sm">
                        Simpan Profile
                    </button>
                </div>
            </form>

            {{-- FORM UPDATE PASSWORD --}}
            <form method="POST"
                  action="{{ route('profile.profilecustomer.password', ['token' => request('token')]) }}"
                  class="px-8 pb-8 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="token" value="{{ request('token') }}">

                <div class="border-t border-slate-200 pt-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-1">Ganti Password</h2>
                    <p class="text-sm text-slate-500 mb-4">Isi password lama dan password baru</p>

                    @if(session('success_password'))
                        <div class="bg-green-100 text-green-700 px-4 py-3 rounded-lg text-sm mb-3">
                            {{ session('success_password') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Lama</label>
                            <input type="password" name="current_password"
                                   class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                            @error('current_password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru</label>
                            <input type="password" name="new_password"
                                   class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                            @error('new_password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation"
                                   class="w-full h-12 px-4 rounded-xl border border-slate-300 bg-slate-50">
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-slate-800 text-white hover:bg-slate-900 transition font-semibold text-sm">
                            Update Password
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection