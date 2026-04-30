@extends('layouts.login')

@section('content')

<div class="w-[360px] bg-green-800 text-white rounded-2xl shadow-2xl p-8">

    <!-- HEADER -->
    <div class="flex items-center justify-center gap-4 mb-6">
        <img src="{{ asset('template/dist/assets/compiled/png/logobaru.png') }}"
             class="w-24 h-24 object-contain">

        <div class="text-left leading-tight">
            <h4 class="font-bold text-2xl">NKRI</h4>
            <p class="text-sm text-green-200 tracking-wide">
                KANTIN UPNVJT
            </p>
        </div>
    </div>

    <!-- ERROR -->
    <div id="errorBox"
         class="hidden mb-4 text-sm bg-red-500/20 text-red-200 border border-red-400/40 px-3 py-2 rounded-lg">
    </div>

    <!-- FORM -->
    <form id="loginForm" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm mb-1 text-green-200">Username</label>
            <input type="text" name="username" required
                class="w-full px-4 py-2 rounded-lg bg-green-900 border border-green-600">
        </div>

        <div>
            <label class="block text-sm mb-1 text-green-200">Password</label>
            <input type="password" name="password" required
                class="w-full px-4 py-2 rounded-lg bg-green-900 border border-green-600">
        </div>

        <button type="submit"
            class="w-full mt-2 py-2 rounded-lg bg-green-400 hover:bg-green-300 text-green-900 font-semibold">
            Masuk
        </button>

        <div class="text-center mt-4 text-sm text-green-200">
            Belum punya akun?
            <a href="/register" class="text-green-300 hover:underline">
                Daftar di sini
            </a>
        </div>
    </form>

</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e){
    e.preventDefault();

    const username = document.querySelector('[name="username"]').value;
    const password = document.querySelector('[name="password"]').value;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                 || document.querySelector('[name="_token"]')?.value;

    try {
        const res = await fetch('/api/login',{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            credentials: 'same-origin', // penting agar session cookie tersimpan
            body: JSON.stringify({ username, password })
        });

        const data = await res.json();

        if(!res.ok || !data.success){
            document.getElementById('errorBox').classList.remove('hidden');
            document.getElementById('errorBox').innerText = data.message || 'Login gagal';
            return;
        }

        // Optional token untuk konsumsi API via JS
        localStorage.setItem('token', data.token);
        localStorage.setItem('role', data.role);

        // redirect sesuai role
        if(data.role === 'customer'){
            window.location.href = '/customer/home';
            return;
        }

        if(data.role === 'penjual'){
            window.location.href = '/penjual/home';
            return;
        }

        if(data.role === 'superadmin'){
            window.location.href = '/superadmin/dashboard';
            return;
        }

        // fallback
        window.location.href = '/';
    } catch (error) {
        document.getElementById('errorBox').classList.remove('hidden');
        document.getElementById('errorBox').innerText = 'Terjadi kesalahan server';
    }
});
</script>

@endsection