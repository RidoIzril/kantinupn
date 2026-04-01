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
        <img src="{{ asset('template/dist/assets/compiled/png/LogoKantin.png') }}"
             class="w-10 h-10 object-contain">
        <div>
            <h1 class="text-lg font-bold">NKRI</h1>
            <p class="text-xs text-green-200">Kantin UPNVJT</p>
        </div>
    </div>

    {{-- MENU --}}
    <nav class="flex-1 px-4 py-6 space-y-2">

        {{-- Dashboard --}}
        <a href="{{ route('penjual.homepenjual') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ request()->routeIs('penjual.homepenjual')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <i class="bi bi-speedometer2 text-lg"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        {{-- Produk --}}
        <a href="{{ route('produk.list_produk') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ request()->routeIs('produk.list_produk*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <i class="bi bi-box-seam text-lg"></i>
            <span class="text-sm font-medium">Menu</span>
        </a>

        {{-- Payment --}}
        <a href="{{ route('payment.list_payment') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ request()->routeIs('payment.*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <i class="bi bi-credit-card text-lg"></i>
            <span class="text-sm font-medium">Payment</span>
        </a>

        {{-- Transaksi --}}
        <a href="{{ route('penjual.transaction_manage.manage') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ request()->routeIs('penjual.transaction_manage.*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <i class="bi bi-receipt text-lg"></i>
            <span class="text-sm font-medium">Transaksi</span>
        </a>

        <hr class="border-green-700 my-4">

        {{-- Logout --}}
        <button type="button" onclick="handleLogout()"
            class="w-full flex items-center gap-3 px-4 py-2 rounded-lg
                   text-red-300 hover:bg-red-500 hover:text-white transition">
            <i class="bi bi-box-arrow-left text-lg"></i>
            <span class="text-sm font-medium">Logout</span>
        </button>
    </nav>

    {{-- FOOTER --}}
    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>
<script>
async function handleLogout() {
    const token = localStorage.getItem('token');

    try {
        if (token) {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
        }
    } catch (e) {
        console.error(e);
    } finally {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        window.location.href = '/login';
    }
}
</script>