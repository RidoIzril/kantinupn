{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','KANTIN NKRI')</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
    @php
        $isPenjualPath    = request()->is('penjual/*');
        $isSuperadminPath = request()->is('superadmin/*');
        $isCustomerPath   = request()->is('customer/*');
        $isRootLanding    = request()->is('/');

        $isCustomerArea = $isRootLanding || $isCustomerPath;
        $hasSidebar = $isPenjualPath || $isSuperadminPath || $isCustomerArea;
    @endphp

    {{-- SIDEBAR --}}
    @if ($isPenjualPath)
        @include('penjual.sidebarpenjual')
    @elseif ($isSuperadminPath)
        @include('superadmin.sidebarsuperadmin')
    @elseif ($isCustomerArea)
        @include('customer.sidebarcus')
    @endif

    <main class="min-h-screen bg-gray-100 transition-all duration-300 {{ $hasSidebar ? 'pl-0 md:pl-64' : 'pl-0' }}">
        <div class="p-6">
            @yield('content')
        </div>
    </main>

    <script>
    (function authSessionManager() {
        const SESSION_TTL_HOURS = 4;
        const TTL_MS = SESSION_TTL_HOURS * 60 * 60 * 1000;

        const path = window.location.pathname;
        const now = Date.now();

        function clearAuth() {
            localStorage.removeItem('token');
            localStorage.removeItem('role');
            localStorage.removeItem('token_issued_at');
        }

        let token = localStorage.getItem('token');
        let role = (localStorage.getItem('role') || '').trim().toLowerCase();
        let issuedAt = parseInt(localStorage.getItem('token_issued_at') || '0', 10);

        // token lama belum ada timestamp
        if (token && !issuedAt) {
            issuedAt = now;
            localStorage.setItem('token_issued_at', String(now));
        }

        // expire check
        if (token && issuedAt && (now - issuedAt > TTL_MS)) {
            clearAuth();
            token = null;
            role = '';
        }

        const isCustomer = !!token && role === 'customer';
        const isPenjual  = !!token && role === 'penjual';

        // kalau sudah login customer, masuk login/register => lempar ke home
        if ((path === '/login' || path === '/register') && isCustomer) {
            window.location.replace('/customer/home');
            return;
        }

        // root "/" jika customer => home customer
        if (path === '/' && isCustomer) {
            window.location.replace('/customer/home');
            return;
        }

        // guard area penjual
        if (path.startsWith('/penjual') && !isPenjual) {
            window.location.replace('/login');
            return;
        }

        // NOTE:
        // area customer sengaja tidak dipaksa redirect di layout,
        // karena flow kamu: guest boleh lihat beberapa halaman dulu.
    })();
    </script>

    @stack('scripts')
</body>
</html>