{{-- MOBILE TOPBAR + HAMBURGER --}}
<div id="mobile-topbar"
     class="md:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 transition-all duration-300 ease-in-out">
    <div class="h-14 px-4 flex items-center justify-between">
        <button id="sidebar-toggle"
                onclick="toggleSidebar()"
                class="bg-green-700 text-white px-3 py-2 rounded-lg shadow transition-all duration-200 active:scale-95">
            ☰
        </button>

        <div class="flex items-center gap-2">
            <img src="{{ asset('template/dist/assets/compiled/png/LogoKantin.png') }}"
                 class="w-7 h-7 object-contain" alt="Logo">
            <span class="font-semibold text-slate-700">Penjual</span>
        </div>
    </div>
</div>

{{-- OVERLAY MOBILE --}}
<div id="sidebar-overlay"
     class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity duration-300"
     onclick="toggleSidebar()"></div>

{{-- SIDEBAR --}}
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-green-800 text-white
              transform -translate-x-full md:translate-x-0
              transition-transform duration-300 ease-in-out
              flex flex-col">

    <div class="flex items-center gap-3 px-6 py-5 border-b border-green-700">
        <img src="{{ asset('template/dist/assets/compiled/png/LogoKantin.png') }}" class="w-10 h-10 object-contain" alt="Logo">
        <div>
            <h1 class="text-lg font-bold">NKRI</h1>
            <p class="text-xs text-green-200">Kantin UPNVJT</p>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-2">
        <a data-protected="1" href="{{ route('penjual.homepenjual') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
                  text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]">
            <i class="bi bi-speedometer2 text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <a data-protected="1" href="{{ route('produk.list_produk') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
                  text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]">
            <i class="bi bi-box-seam text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Menu</span>
        </a>

        <a data-protected="1" href="{{ route('payment.list_payment') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
                  text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]">
            <i class="bi bi-credit-card text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Payment</span>
        </a>

        <a data-protected="1" href="{{ route('penjual.transaction_manage.manage') }}"
           class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
                  text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]">
            <i class="bi bi-receipt text-lg transition-transform duration-200 group-hover:rotate-3"></i>
            <span class="text-sm font-medium">Transaksi</span>
        </a>

        <a data-protected="1" href="{{ route('penjual.profile.edit') }}"
   class="menu-link group flex items-center gap-3 px-4 py-2 rounded-lg transition-all duration-200 ease-out
          text-green-200 hover:bg-green-700 hover:text-white hover:translate-x-1 active:scale-[0.98]">
    <i class="bi bi-person-circle text-lg transition-transform duration-200 group-hover:rotate-3"></i>
    <span class="text-sm font-medium">Profile</span>
</a>

        <hr class="border-green-700 my-4">

        <button type="button" onclick="handleLogout()"
            class="w-full flex items-center gap-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-500 hover:text-white transition-all duration-200 active:scale-[0.98]">
            <i class="bi bi-box-arrow-left text-lg"></i>
            <span class="text-sm font-medium">Logout</span>
        </button>
    </nav>

    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>

@push('scripts')
<script>
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

(function attachTokenAndActiveState() {
    const token = localStorage.getItem('token');
    const currentUrl = new URL(window.location.href);

    document.querySelectorAll('a[data-protected="1"]').forEach(a => {
        if (token) {
            const url = new URL(a.href, window.location.origin);
            url.searchParams.set('token', token);
            a.href = url.toString();
        }

        const aUrl = new URL(a.href, window.location.origin);
        if (aUrl.pathname === currentUrl.pathname) {
            a.classList.remove('text-green-200');
            a.classList.add('bg-green-700', 'text-white', 'shadow-md', 'translate-x-1');
        }

        a.addEventListener('click', function () {
            a.classList.add('ring-2', 'ring-white/30');
            setTimeout(() => a.classList.remove('ring-2', 'ring-white/30'), 220);

            if (window.innerWidth < 768) {
                setTimeout(() => toggleSidebar(), 120);
            }
        });
    });
})();

async function handleLogout() {
    const token = localStorage.getItem('token');
    const logoutUrl = "{{ url('/api/logout') }}";
    const loginUrl  = "{{ url('/login') }}";

    try {
        if (token) {
            await fetch(logoutUrl, {
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
        window.location.replace(loginUrl);
    }
}

window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
});
</script>
@endpush