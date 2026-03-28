{{-- OVERLAY MOBILE --}}
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"
     onclick="toggleSidebar()">
</div>

{{-- SIDEBAR --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 w-64
              bg-green-800 text-white
              transform -translate-x-full md:translate-x-0
              transition-transform duration-300 ease-in-out
              flex flex-col">

    {{-- LOGO --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-green-700">
        <img src="{{ asset('template/dist/assets/compiled/png/Logokantin.png') }}"
             class="w-10 h-10 object-contain">

        <div>
            <h1 class="text-lg font-bold">NKRI</h1>
            <p class="text-xs text-green-200">Customer</p>
        </div>
    </div>

    {{-- MENU --}}
    <nav class="flex-1 px-4 py-6 space-y-2">

        {{-- PRODUK --}}
        <a href="{{ route('customer.homecustomer') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ Request::is('customer/home*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <span class="text-lg">🛍️</span>
            <span class="text-sm font-medium">Produk</span>
        </a>

        {{-- KERANJANG --}}
        <a href="{{ route('carts.cartcustomer') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ Request::is('customer/keranjang*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <span class="text-lg">🛒</span>
            <span class="text-sm font-medium">Keranjang</span>
        </a>

        {{-- TRANSAKSI --}}
        <a href="{{ route('transactions.list_transaction') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ Request::is('customer/transaksi*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <span class="text-lg">💳</span>
            <span class="text-sm font-medium">Transaksi</span>
        </a>

        <hr class="border-green-700 my-4">

        {{-- PROFILE --}}
        <a href="{{ route('profile.profilecustomer') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ Request::is('customer/profile*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <span class="text-lg">👤</span>
            <span class="text-sm font-medium">Profile</span>
        </a>

        {{-- LOGOUT --}}
        <button onclick="logout()"
    class="w-full flex items-center gap-3 px-4 py-2 rounded-lg
           text-red-300 hover:bg-red-500 hover:text-white transition mt-4">
    <span class="text-lg">🚪</span>
    <span class="text-sm font-medium">Logout</span>
</button>

<script>
function logout(){
    const token = localStorage.getItem('token');

    fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token
        }
    })
    .then(() => {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        window.location.href = '/login';
    });
}
</script>
    </nav>

    {{-- FOOTER --}}
    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>
