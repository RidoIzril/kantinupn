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
            <p class="text-xs text-green-200">Superadmin</p>
        </div>
    </div>

    {{-- MENU --}}
    <nav class="flex-1 px-4 py-6 space-y-2">

        {{-- Dashboard --}}
        <a href="{{ route('superadmin.homesuperadmin') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ request()->routeIs('superadmin.homesuperadmin')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <i class="bi bi-speedometer2 text-lg"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        {{-- Kategori --}}
        <a href="{{ route('superadmin.kategori.list_kategori') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition
           {{ request()->routeIs('superadmin.kategori.*')
                ? 'bg-green-700 text-white'
                : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
            <i class="bi bi-tags text-lg"></i>
            <span class="text-sm font-medium">Kategori</span>
        </a>

        {{-- Akun Penjual --}}
<a href="{{ route('superadmin.penjual.index') }}"
   class="flex items-center gap-3 px-4 py-2 rounded-lg transition
   {{ request()->routeIs('superadmin.penjual.*')
        ? 'bg-green-700 text-white'
        : 'text-green-200 hover:bg-green-700 hover:text-white' }}">
    <i class="bi bi-shop text-lg"></i>
    <span class="text-sm font-medium">Akun Penjual</span>
</a>

        <hr class="border-green-700 my-4">

        {{-- Logout --}}
        <button onclick="logout()"
    class="w-full flex items-center gap-3 px-4 py-2 rounded-lg
           text-red-300 hover:bg-red-500 hover:text-white transition mt-4">
    <span class="text-lg">🚪</span>
    <span class="text-sm font-medium">Logout</span>
</button>
    </nav>

    <script>
function logout() {
    fetch('/api/logout', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);

        // hapus token
        localStorage.removeItem('token');

        // redirect ke login
        window.location.href = '/login';
    })
    .catch(err => console.error(err));
}
</script>

    {{-- FOOTER --}}
    <div class="px-6 py-4 border-t border-green-700 text-xs text-green-300">
        © {{ date('Y') }} Kantin NKRI
    </div>
</aside>
