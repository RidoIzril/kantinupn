@extends('layouts.login')

@section('content')

<div class="w-[360px] bg-green-800 text-white rounded-2xl shadow-2xl p-8">

    <h2 class="text-2xl font-bold text-center mb-6">Registrasi Customer</h2>

    <!-- ERROR -->
    <div id="errorBox"
         class="hidden mb-4 text-sm bg-red-500/20 text-red-200 border border-red-400/40 px-3 py-2 rounded-lg">
    </div>

    <!-- SUCCESS -->
    <div id="successBox"
         class="hidden mb-4 text-sm bg-green-500/20 text-green-200 border border-green-400/40 px-3 py-2 rounded-lg">
    </div>

    <form id="registerForm" class="space-y-4">

        <input type="text" name="username" placeholder="Username" required
            class="w-full px-4 py-2 rounded-lg bg-green-900 border border-green-600">

        <input type="password" name="password" placeholder="Password" required
            class="w-full px-4 py-2 rounded-lg bg-green-900 border border-green-600">

        <button type="submit"
            class="w-full py-2 rounded-lg bg-green-400 hover:bg-green-300 text-green-900 font-semibold">
            Daftar
        </button>

    </form>

    <div class="text-center mt-4 text-sm text-green-200">
        Sudah punya akun?
        <a href="/login" class="text-green-300 hover:underline">
            Login
        </a>
    </div>

</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e){

    e.preventDefault();

    const username = document.querySelector('[name="username"]').value;
    const password = document.querySelector('[name="password"]').value;

    const res = await fetch('/api/register',{
        method:'POST',
        headers:{
            'Content-Type':'application/json'
        },
        body: JSON.stringify({
            username: username,
            password: password
        })
    });

    const data = await res.json();

    if(!data.success){
        document.getElementById('errorBox').classList.remove('hidden');
        document.getElementById('errorBox').innerText = data.message;
        return;
    }

    document.getElementById('successBox').classList.remove('hidden');
    document.getElementById('successBox').innerText = 'Registrasi berhasil, silakan login';

    setTimeout(()=>{
        window.location.href = '/login';
    },1500);

});
</script>

@endsection