@extends('layouts.login')

@section('content')
<div class="w-[360px] bg-green-800 text-white rounded-2xl shadow-2xl p-8">

    <!-- HEADER LOGO -->
    <div class="flex items-center justify-center gap-4 mb-6">
        <img
            src="{{ asset('template/dist/assets/compiled/png/LogoKantin.png') }}"
            alt="Logo NKRI"
            class="w-24 h-24 object-contain flex-shrink-0"
        >

        <div class="text-left leading-tight">
            <h4 class="font-bold text-2xl text-white">NKRI</h4>
            <p class="text-sm text-green-200 tracking-wide">
                KANTIN UPNVJT
            </p>
        </div>
    </div>

    {{-- ✅ SUCCESS ALERT --}}
    @if (session('success'))
        <div class="mb-4 text-sm
                    bg-green-500/20 text-green-100
                    border border-green-400/40
                    px-3 py-2 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- ❌ ERROR LOGIN --}}
    @if ($errors->any())
        <div class="mb-4 text-sm
                    bg-red-500/20 text-red-200
                    border border-red-400/40
                    px-3 py-2 rounded-lg">
            Username atau password salah.
        </div>
    @endif

    <!-- FORM -->
    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm mb-1 text-green-200">Username</label>
            <input type="text" name="user_name"
                class="w-full px-4 py-2 rounded-lg
                       bg-green-900 border border-green-600
                       text-white placeholder-green-300
                       focus:outline-none focus:border-green-300"
                placeholder="Masukkan username" required>
        </div>

        <div>
            <label class="block text-sm mb-1 text-green-200">Password</label>
            <input type="password" name="user_password"
                class="w-full px-4 py-2 rounded-lg
                       bg-green-900 border border-green-600
                       text-white placeholder-green-300
                       focus:outline-none focus:border-green-300"
                placeholder="Masukkan password" required>
        </div>

        <button type="submit"
            class="w-full mt-2 py-2 rounded-lg
                   bg-green-400 hover:bg-green-300
                   text-green-900 font-semibold transition">
            Masuk
        </button>
    </form>

    <hr class="my-5 border-green-600">

    <p class="text-sm text-center text-green-200">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-green-300 hover:underline">
            Daftar
        </a>
    </p>

</div>
@endsection
