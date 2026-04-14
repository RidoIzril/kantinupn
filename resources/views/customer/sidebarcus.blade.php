{{-- MOBILE TOPBAR + HAMBURGER --}}
<div id="mobile-topbar"
     class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 transition-all duration-300 ease-in-out">
    <div class="h-14 px-4 flex items-center justify-between">
        <button id="sidebar-toggle"
                onclick="toggleSidebar()"
                class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-700 hover:bg-green-800 active:scale-95 text-white shadow-md transition-all duration-200">
            <span class="text-xl leading-none">☰</span>
        </button>

        <div class="flex items-center gap-2">
            <img src="{{ asset('template/dist/assets/compiled/png/Logokantin.png') }}"
                 class="w-7 h-7 object-contain" alt="Logo">
            <span class="font-semibold text-slate-700" id="topbar-role-label">Guest</span>
        </div>
    </div>
</div>

{{-- OVERLAY MOBILE --}}
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-50 hidden md:hidden transition-opacity duration-300"
     onclick="toggleSidebar()"></div>

{{-- SIDEBAR --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-[70] w-64 bg-green-800 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">

    {{-- HEADER --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-green-700">
        <img src="{{ asset('template/dist/assets/compiled/png/Logokantin.png') }}"
             class="w-10 h-10 object-contain" alt="Logo">
        <div>
            <h1 class="text-lg font-bold">NKRI</h1>
            <p class="text-xs text-green-200" id="sidebar-role-label">Guest</p>
        </div>
    </div>

    {{-- MENU CUSTOMER (FULL) --}}
    <nav id="menu-customer" class="hidden flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('customer.homecustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200
           {{ Request::is('/') || Request::is('customer/home*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1' }}">
            <span class="text-lg">🛍️</span>
            <span class="text-sm font-medium">Produk</span>
        </a>

        <a id="menu-keranjang" href="{{ route('carts.cartcustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 transition-all duration-200">
            <span class="text-lg">🛒</span>
            <span class="text-sm font-medium">Keranjang</span>
        </a>

        <a id="menu-transaksi" href="{{ route('transactions.list_transaction') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 transition-all duration-200">
            <span class="text-lg">💳</span>
            <span class="text-sm font-medium">Transaksi</span>
        </a>

        <hr class="border-green-700 my-4">

        <a id="menu-profile" href="{{ route('profile.profilecustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 transition-all duration-200">
            <span class="text-lg">👤</span>
            <span class="text-sm font-medium">Profile</span>
        </a>

        <button id="menu-logout" onclick="logout()"
                class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-500 hover:text-white transition-all duration-200 mt-4">
            <span class="text-lg">🚪</span>
            <span class="text-sm font-medium">Logout</span>
        </button>
    </nav>

    {{-- MENU GUEST (MINIMAL) --}}
    <nav id="menu-guest" class="flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('login') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-green-100 hover:bg-green-700 hover:text-white transition-all duration-200">
            <span class="text-lg">🔐</span>
            <span class="text-sm font-medium">Login</span>
        </a>

        <a href="{{ route('register') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-green-100 hover:bg-green-700 hover:text-white transition-all duration-200">
            <span class="text-lg">📝</span>
            <span class="text-sm font-medium">Register</span>
        </a>
    </nav>

    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>

<script>
(function setupSidebarMode() {
    const SESSION_TTL_HOURS = 4;
    const TTL_MS = SESSION_TTL_HOURS * 60 * 60 * 1000;

    const path = window.location.pathname;
    const isLanding = path === '/';
    const isCustomerArea = path.startsWith('/customer/');

    let token = localStorage.getItem('token') || '';
    let role = localStorage.getItem('role') || '';
    let issuedAt = parseInt(localStorage.getItem('token_issued_at') || '0', 10);

    // token lama yang belum punya issued_at
    if (token && role && !issuedAt) {
        issuedAt = Date.now();
        localStorage.setItem('token_issued_at', String(issuedAt));
    }

    // expire otomatis
    if (token && role && issuedAt && (Date.now() - issuedAt > TTL_MS)) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('token_issued_at');
        token = '';
        role = '';
    }

    // RULE:
    // - "/" selalu tampil guest minimal
    // - "/customer/*" tampil customer kalau token valid + role customer
    const isCustomer = !isLanding && isCustomerArea && !!token && role === 'customer';

    const sidebarRoleLabel = document.getElementById('sidebar-role-label');
    const topbarRoleLabel  = document.getElementById('topbar-role-label');

    const menuCustomer = document.getElementById('menu-customer');
    const menuGuest    = document.getElementById('menu-guest');

    const menuKeranjang = document.getElementById('menu-keranjang');
    const menuTransaksi = document.getElementById('menu-transaksi');
    const menuProfile   = document.getElementById('menu-profile');

    if (isCustomer) {
        sidebarRoleLabel.textContent = 'Customer';
        topbarRoleLabel.textContent  = 'Customer';

        menuCustomer?.classList.remove('hidden');
        menuGuest?.classList.add('hidden');

        [menuKeranjang, menuTransaksi, menuProfile].forEach(a => {
            if (!a) return;
            const url = new URL(a.href, window.location.origin);
            url.searchParams.set('token', token);
            a.href = url.toString();
        });
    } else {
        sidebarRoleLabel.textContent = 'Guest';
        topbarRoleLabel.textContent  = 'Guest';

        menuCustomer?.classList.add('hidden');
        menuGuest?.classList.remove('hidden');
    }
})();

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const isOpen = !sidebar.classList.contains('-translate-x-full');

    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
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
        localStorage.removeItem('token_issued_at');
        window.location.href = '/login';
    });
}
</script>