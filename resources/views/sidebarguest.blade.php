<div class="w-72 bg-green-900 text-white min-h-screen flex flex-col">
    <div class="p-6 border-b border-green-800">
        <h1 class="text-4xl font-bold">NKRI</h1>
        <p class="text-green-100">Guest</p>
    </div>

    <nav class="p-4 space-y-2">
        <a href="{{ url('/') }}" class="block bg-green-700 px-4 py-3 rounded-lg font-semibold">🛍️ Produk</a>
        <a href="{{ route('login') }}" class="block hover:bg-green-800 px-4 py-3 rounded-lg">🛒 Keranjang</a>
        <a href="{{ route('login') }}" class="block hover:bg-green-800 px-4 py-3 rounded-lg">💳 Transaksi</a>

        <div class="border-t border-green-800 my-2"></div>

        <a href="{{ route('login') }}" class="block hover:bg-green-800 px-4 py-3 rounded-lg">👤 Login</a>
        <a href="{{ route('register') }}" class="block hover:bg-green-800 px-4 py-3 rounded-lg">📝 Register</a>
    </nav>

    <div class="mt-auto p-4 text-green-200 text-sm border-t border-green-800">
        © 2026 Kantin NKRI
    </div>
</div>