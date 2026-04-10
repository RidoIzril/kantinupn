{{-- MOBILE TOPBAR + HAMBURGER --}}
<div id="mobile-topbar"
     class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 transition-all duration-300 ease-in-out">
    <div class="h-14 px-4 flex items-center justify-between">
        <button id="sidebar-toggle"
                onclick="toggleSidebar()"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg
                       bg-green-700 hover:bg-green-800 active:scale-95
                       text-white shadow-md transition-all duration-200">
            <span class="text-xl leading-none">☰</span>
        </button>

        <div class="flex items-center gap-2">
            <img src="{{ asset('template/dist/assets/compiled/png/LogoKantin.png') }}"
                 class="w-7 h-7 object-contain" alt="Logo">
            <span class="font-semibold text-slate-700">Superadmin</span>
        </div>
    </div>
</div>

{{-- OVERLAY MOBILE --}}
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-50 hidden md:hidden transition-opacity duration-300"
     onclick="toggleSidebar()">
</div>

{{-- SIDEBAR --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-[70] w-64
              bg-green-800 text-white
              transform -translate-x-full md:translate-x-0
              transition-transform duration-300 ease-in-out
              flex flex-col">

    <div class="flex items-center gap-3 px-6 py-5 border-b border-green-700">
        <img src="{{ asset('template/dist/assets/compiled/png/Logokantin.png') }}"
             class="w-10 h-10 object-contain" alt="Logo">
        <div>
            <h1 class="text-lg font-bold">NKRI</h1>
            <p class="text-xs text-green-200">Customer</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('customer.homecustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
           {{ Request::is('customer/home*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]' }}">
            <span class="text-lg transition-transform duration-200 group-hover:rotate-3">🛍️</span>
            <span class="text-sm font-medium">Produk</span>
        </a>

        <a href="{{ route('carts.cartcustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
           {{ Request::is('customer/keranjang*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]' }}">
            <span class="text-lg transition-transform duration-200 group-hover:rotate-3">🛒</span>
            <span class="text-sm font-medium">Keranjang</span>
        </a>

        <a href="{{ route('transactions.list_transaction') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
           {{ Request::is('customer/transaksi*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]' }}">
            <span class="text-lg transition-transform duration-200 group-hover:rotate-3">💳</span>
            <span class="text-sm font-medium">Transaksi</span>
        </a>

        <hr class="border-green-700 my-4">

        <a href="{{ route('profile.profilecustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
           {{ Request::is('customer/profile*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]' }}">
            <span class="text-lg transition-transform duration-200 group-hover:rotate-3">👤</span>
            <span class="text-sm font-medium">Profile</span>
        </a>

        <button onclick="logout()"
                class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-500 hover:text-white transition-all duration-200 ease-out mt-4 active:scale-[0.98]">
            <span class="text-lg">🚪</span>
            <span class="text-sm font-medium">Logout</span>
        </button>
    </nav>

    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const topbar  = document.getElementById('mobile-topbar');

    const isOpen = !sidebar.classList.contains('-translate-x-full');

    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        topbar.classList.remove('left-64');
        document.body.classList.remove('overflow-hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        topbar.classList.add('left-64');
        document.body.classList.add('overflow-hidden');
    }
}

function logout() {
    const token = localStorage.getItem('token');
    fetch('/api/logout', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + token }
    }).finally(() => {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        window.location.href = '/login';
    });
}

// klik effect (ring) biar sama feel penjual
document.querySelectorAll('.menu-link').forEach(link => {
    link.addEventListener('click', () => {
        link.classList.add('ring-2', 'ring-white/30');
        setTimeout(() => link.classList.remove('ring-2', 'ring-white/30'), 220);
    });
});

window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const topbar  = document.getElementById('mobile-topbar');

    if (window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('hidden');
        topbar.classList.remove('left-64');
        document.body.classList.remove('overflow-hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        topbar.classList.remove('left-64');
    }
});
</script>