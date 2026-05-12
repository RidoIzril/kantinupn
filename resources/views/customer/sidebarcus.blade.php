{{-- MOBILE TOPBAR (Hamburger / Back) --}}
<div id="mobile-topbar"
     class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 transition-all duration-300 ease-in-out">
    <div class="h-14 px-4 flex items-center justify-between">

        {{-- LEFT BUTTON --}}
        @if (View::hasSection('use_back_button'))
            <a id="topbar-back"
               href="@yield('back_url', url()->previous())"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-700 hover:bg-green-800 active:scale-95 text-white shadow-md transition-all duration-200">
                <span class="text-xl leading-none">←</span>
            </a>
        @else
            <button id="sidebar-toggle"
                    onclick="toggleSidebar()"
                    class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-700 hover:bg-green-800 active:scale-95 text-white shadow-md transition-all duration-200">
                <span class="text-xl leading-none">☰</span>
            </button>
        @endif

        <div class="flex items-center gap-2">
            <img src="{{ asset('template/dist/assets/compiled/png/logobaru.png') }}"
                 class="w-7 h-7 object-contain" alt="Logo">
            <span class="font-semibold text-slate-700" id="topbar-role-label">Guest</span>
        </div>
    </div>
</div>

{{-- OVERLAY MOBILE (disable kalau mode back) --}}
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-50 hidden md:hidden transition-opacity duration-300"
     @if (!View::hasSection('use_back_button'))
        onclick="toggleSidebar()"
     @endif
></div>

{{-- SIDEBAR --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-[70] w-64 bg-green-800 text-white transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">

    {{-- HEADER --}}
    <div class="flex items-center gap-3 px-6 py-5 border-b border-green-700">
        <img src="{{ asset('template/dist/assets/compiled/png/logobaru.png') }}"
             class="w-10 h-10 object-contain" alt="Logo">
        <div>
            <h1 class="text-lg font-bold">NKRI</h1>
            <p class="text-xs text-green-200" id="sidebar-role-label">Guest</p>
        </div>
    </div>

    {{-- MENU CUSTOMER --}}
    <nav id="menu-customer" class="hidden flex-1 px-4 py-6 space-y-2">

        <a id="menu-dashboard" href="{{ route('customer.homecustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200
           {{ Request::is('/') || Request::is('customer/home*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1' }}">
            <i class="bi bi-speedometer2 text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <a id="menu-keranjang" href="{{ route('carts.cartcustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200
           {{ Request::is('customer/keranjang*') || Request::is('customer/cart*') || Request::is('customer/carts*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1' }}">
            <i class="bi bi-cart text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Keranjang</span>
        </a>

        <a id="menu-riwayat" href="{{ route('orders.history') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200
           {{ Request::is('customer/riwayat-pesanan*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1' }}">
            <i class="bi bi-clock-history text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Riwayat Pesanan</span>
        </a>

        {{-- ✅ MENU CHAT + BADGE --}}
        <a id="menu-chat" href="{{ route('chat.list') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200
           {{ Request::is('customer/chat*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1' }}">

            <div class="relative">
                <i class="bi bi-chat-dots text-lg transition-transform duration-200 group-hover:rotate-3"></i>

                {{-- BADGE --}}
                <span id="sidebar-chat-badge"
                      class="hidden absolute -top-2 -right-3 min-w-5 h-5 px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold">
                    0
                </span>
            </div>

            <span class="text-sm font-medium">Live Chat</span>
        </a>

        <hr class="border-green-700 my-4">

        <a id="menu-profile" href="{{ route('profile.profilecustomer') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200
           {{ Request::is('customer/profile*') ? 'bg-green-700 text-white shadow-md translate-x-1' : 'text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1' }}">
            <i class="bi bi-person-circle text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Profile</span>
        </a>

        <button id="menu-logout" onclick="logout()"
                class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-500 hover:text-white transition-all duration-200 mt-4">
            <i class="bi bi-box-arrow-left text-lg"></i>
            <span class="text-sm font-medium">Logout</span>
        </button>
    </nav>

    {{-- MENU GUEST --}}
    <nav id="menu-guest" class="flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('login') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-green-100 hover:bg-green-700 hover:text-white transition-all duration-200">
            <i class="bi bi-box-arrow-in-right text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Login</span>
        </a>

        <a href="{{ route('register') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-green-100 hover:bg-green-700 hover:text-white transition-all duration-200">
            <i class="bi bi-person-plus text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Register</span>
        </a>
    </nav>

    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>

<script>
(function () {

    let token = localStorage.getItem('token') || '';
    let role  = localStorage.getItem('role') || '';

    const isCustomer = token && role === 'customer';

    const menuCustomer = document.getElementById('menu-customer');
    const menuGuest    = document.getElementById('menu-guest');

    const menuIds = [
        'menu-dashboard',
        'menu-keranjang',
        'menu-riwayat',
        'menu-transaksi',
        'menu-profile',
        'menu-chat'
    ];

    if (isCustomer) {

        menuCustomer.classList.remove('hidden');
        menuGuest.classList.add('hidden');

        menuIds.forEach(id => {

            let el = document.getElementById(id);

            if (!el) return;

            let url = new URL(el.href, window.location.origin);

            url.searchParams.set('token', token);

            el.href = url.toString();
        });

    } else {

        menuCustomer.classList.add('hidden');
        menuGuest.classList.remove('hidden');
    }

})();

function loadUserInfo() {

    let token = localStorage.getItem('token');

    if (!token) {
        setGuest();
        return;
    }

    fetch(`/api/me-token?token=${token}`)
        .then(res => res.json())
        .then(data => {

            if (!data || !data.nama_lengkap) {
                setGuest();
                return;
            }

            const topbar = document.getElementById('topbar-role-label');
            const sidebar = document.getElementById('sidebar-role-label');

            if (topbar) topbar.innerText = data.nama_lengkap;
            if (sidebar) sidebar.innerText = data.nama_lengkap;

        })
        .catch(() => setGuest());
}

function setGuest() {

    const topbar = document.getElementById('topbar-role-label');
    const sidebar = document.getElementById('sidebar-role-label');

    if (topbar) topbar.innerText = "Guest";
    if (sidebar) sidebar.innerText = "Guest";
}

loadUserInfo();

function logout() {

    localStorage.removeItem('token');
    localStorage.removeItem('role');

    window.location.href = "/login";
}

function toggleSidebar() {

    // kalau mode back, jangan toggle (safety)
    @if (View::hasSection('use_back_button'))
        return;
    @endif

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    const isOpen = !sidebar.classList.contains('-translate-x-full');

    if (isOpen) {

        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');

    } else {

        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }
}

{{-- ✅ TAMBAHAN UNREAD BADGE CHAT --}}
function refreshUnreadBadge() {

    const token = localStorage.getItem('token') || '';
    const role  = localStorage.getItem('role') || '';

    if (!token || role !== 'customer') return;

    fetch(`/customer/chat/unread-count?token=${encodeURIComponent(token)}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {

        const badge = document.getElementById('sidebar-chat-badge');

        if (!badge) return;

        const count = Number(data.count || 0);

        if (count > 0) {

            badge.classList.remove('hidden');
            badge.textContent = count > 99 ? '99+' : String(count);

        } else {

            badge.classList.add('hidden');
            badge.textContent = '0';
        }
    })
    .catch(() => {});
}

document.addEventListener('DOMContentLoaded', function () {

    refreshUnreadBadge();

    setInterval(refreshUnreadBadge, 5000);
});
</script>