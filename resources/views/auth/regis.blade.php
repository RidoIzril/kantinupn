@extends('layouts.login')

@section('content')
<div class="w-full max-w-3xl bg-green-800 rounded-2xl shadow-2xl p-8 text-green-100">

    {{-- HEADER --}}
    <div class="flex items-center justify-center gap-4 mb-8">
        <img
            src="{{ asset('template/dist/assets/compiled/png/LogoKantin.png') }}"
            alt="Logo"
            class="w-20 h-20 object-contain opacity-90"
        >
        <div class="leading-tight">
            <h5 class="text-lg font-semibold">NKRI</h5>
            <p class="text-sm text-green-300">KANTIN UPNVJT</p>
        </div>
    </div>

    <h2 class="text-center text-lg font-semibold mb-6">
        Registrasi Akun Customer
    </h2>

    {{-- FORM --}}
    <form action="{{ route('register') }}" method="POST"
          class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @csrf

        {{-- Username --}}
        <div>
            <label class="block text-sm mb-1">Username</label>
            <input type="text" name="customer_username" required
                   placeholder="Masukkan username"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Nama Lengkap --}}
        <div>
            <label class="block text-sm mb-1">Nama Lengkap</label>
            <input type="text" name="customer_fullname" required
                   placeholder="Nama lengkap"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Email --}}
        <div>
            <label class="block text-sm mb-1">Email</label>
            <input type="email" name="customer_email" required
                   placeholder="Masukkan email"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- No Telepon --}}
        <div>
            <label class="block text-sm mb-1">No Telepon</label>
            <input type="text" name="customer_contact" required
                   placeholder="08xxxxxxxxxx"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Password --}}
        <div>
            <label class="block text-sm mb-1">Password</label>
            <input type="password" name="customer_password" required
                   placeholder="Masukkan password"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Konfirmasi Password --}}
        <div>
            <label class="block text-sm mb-1">Konfirmasi Password</label>
            <input type="password" name="customer_confirm" required
                   placeholder="Konfirmasi password"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Tanggal Lahir --}}
        <div>
            <label class="block text-sm mb-1">Tanggal Lahir</label>
            <input type="date" name="customer_dob" required
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Gender --}}
        <div>
            <label class="block text-sm mb-1">Gender</label>
            <select name="customer_gender" required
                    class="w-full px-4 py-2 rounded-lg
                           bg-green-900 border border-green-600
                           text-green-100
                           focus:outline-none focus:ring-2 focus:ring-green-400">
                <option value="">-- Pilih Gender --</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>
        </div>

        {{-- Fakultas --}}
        <div>
            <label class="block text-sm mb-1">Fakultas</label>
            <input type="text" name="customer_faculty" required
                   placeholder="Fakultas"
                   class="w-full px-4 py-2 rounded-lg
                          bg-green-900 border border-green-600
                          placeholder-green-300 text-green-100
                          focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>

        {{-- Status --}}
        <div>
            <label class="block text-sm mb-1">Status</label>
            <select name="customer_status" required
                    class="w-full px-4 py-2 rounded-lg
                           bg-green-900 border border-green-600
                           text-green-100
                           focus:outline-none focus:ring-2 focus:ring-green-400">
                <option value="">-- Pilih Status --</option>
                <option value="Mahasiswa">Mahasiswa</option>
                <option value="Dosen">Dosen</option>
                <option value="Staff">Tendik</option>
            </select>
        </div>

        {{-- SUBMIT --}}
        <div class="md:col-span-2 mt-4">
            <button type="submit"
                    class="w-full py-3 rounded-lg
                           bg-green-400 hover:bg-green-500
                           text-green-900 font-semibold transition">
                Daftar
            </button>
        </div>
    </form>

    <hr class="my-6 border-green-600">

    {{-- LOGIN --}}
    <div class="text-center text-sm text-green-200">
        Sudah punya akun?
        <a href="{{ route('login') }}"
           class="text-green-400 font-medium hover:underline">
            Masuk
        </a>
    </div>
</div>
@endsection
