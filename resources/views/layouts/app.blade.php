<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<title>@yield('title','KANTIN NKRI')</title>

@vite([
    'resources/css/app.css',
    'resources/js/app.js'
])

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
```

</head>

<body class="bg-gray-100 min-h-screen font-sans">

```
{{-- ===============================
    SIDEBAR BASED ON GUARD SESSION
=============================== --}}

@auth('penjual')
    @include('penjual.sidebarpenjual')
@endauth

@auth('superadmin')
    @include('superadmin.sidebarsuperadmin')
@endauth

@auth('customer')
    @include('customer.sidebarcus')
@endauth



{{-- ===============================
    MAIN CONTENT
=============================== --}}
<main
    class="
        min-h-screen
        bg-gray-100
        transition-all
        duration-300
        pl-0
        md:pl-64
    "
>

    {{-- TOPBAR MOBILE --}}
    @if(
        auth('penjual')->check()
        || auth('superadmin')->check()
        || auth('customer')->check()
    )
    <div class="md:hidden flex items-center gap-3 bg-white px-4 py-3 shadow sticky top-0 z-40">
        <button onclick="toggleSidebar()" class="text-2xl">
            ☰
        </button>

        <span class="font-semibold text-gray-700">

            @auth('superadmin')
                Superadmin
            @endauth

            @auth('penjual')
                Penjual
            @endauth

            @auth('customer')
                Customer
            @endauth

        </span>
    </div>
    @endif


    <div class="p-6">
        @yield('content')
    </div>

</main>
```

<script>

function toggleSidebar(){

    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');

    if(sidebar){
        sidebar.classList.toggle('-translate-x-full');
    }

    if(overlay){
        overlay.classList.toggle('hidden');
    }

}

</script>

</body>
</html>
