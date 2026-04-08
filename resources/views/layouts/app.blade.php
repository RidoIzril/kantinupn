<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title','KANTIN NKRI')</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 min-h-screen font-sans">

    {{-- SIDEBAR by URL prefix --}}
    @if (request()->is('penjual/*'))
        @include('penjual.sidebarpenjual')
    @elseif (request()->is('superadmin/*'))
        @include('superadmin.sidebarsuperadmin')
    @elseif (request()->is('customer/*'))
        @include('customer.sidebarcus')
    @endif

    <main class="min-h-screen bg-gray-100 transition-all duration-300 pl-0 md:pl-64">
        {{-- TOPBAR MOBILE --}}
        @if (
            request()->is('penjual/*') ||
            request()->is('superadmin/*') ||
            request()->is('customer/*')
        )
        <div class="md:hidden flex items-center gap-3 bg-white px-4 py-3 shadow sticky top-0 z-40">
            <button onclick="toggleSidebar()" class="text-2xl">☰</button>

            <span class="font-semibold text-gray-700">
                @if (request()->is('superadmin/*'))
                    Superadmin
                @elseif (request()->is('penjual/*'))
                    Penjual
                @elseif (request()->is('customer/*'))
                    Customer
                @endif
            </span>
        </div>
        @endif

        <div class="p-6">
            @yield('content')
        </div>
    </main>

    <script>
    (function () {
        const token = localStorage.getItem('token');
        const role  = localStorage.getItem('role');
        const path  = window.location.pathname;

        const publicPaths = ['/login', '/register'];
        if (publicPaths.includes(path)) return;

        if (path.startsWith('/penjual') && (!token || role !== 'penjual')) {
            window.location.replace('/login');
            return;
        }
    })();
    </script>

    @stack('scripts')
</body>
</html>