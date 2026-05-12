@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-16 md:mt-6 bg-white shadow rounded-xl p-4">

    <h2 class="text-lg font-bold mb-4">Live Chat</h2>

    <div class="space-y-2">

        @forelse($listUser as $user)
        <a href="{{ route('chat.room', $user->users_id) }}?token={{ request('token') }}"
           class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-100 transition">

            <!-- avatar -->
            <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200">
                @if($user->tenant && $user->tenant->foto_tenant)
                    <img src="{{ asset('storage/' . $user->tenant->foto_tenant) }}"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-green-500"></div>
                @endif
            </div>

            <!-- info -->
            <div>
                <div class="font-semibold">
                    {{ $user->tenant->tenant_name ?? 'Tenant' }}
                </div>

                <div class="text-sm text-gray-500">
                    Klik untuk chat
                </div>
            </div>

            {{-- ✅ BADGE UNREAD PER TENANT --}}
            <div class="ml-auto">
                <span id="unread-{{ $user->users_id }}"
                      class="hidden min-w-6 h-6 px-2 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-bold">
                    0
                </span>
            </div>

        </a>
        @empty

        <p class="text-gray-500 text-center py-10">
            Belum ada chat
        </p>

        @endforelse

    </div>
</div>

{{-- ✅ POLLING UNREAD PER TENANT --}}
<script>

function getToken() {

    const url = new URL(window.location.href);

    return url.searchParams.get('token')
        || localStorage.getItem('token')
        || "";
}

function refreshUnreadByTenant() {

    const token = getToken();
    const role = localStorage.getItem('role') || '';

    if (!token || role !== 'customer') return;

    fetch(`/customer/chat/unread-by-tenant?token=${encodeURIComponent(token)}`, {
        headers: {
            'Accept': 'application/json'
        },
        cache: 'no-store'
    })

    .then(res => res.json())

    .then(data => {

        const counts = (data && data.counts)
            ? data.counts
            : {};

        // ✅ update semua badge realtime
        document.querySelectorAll('[id^="unread-"]').forEach(el => {

            const userId = el.id.replace('unread-', '');

            const n = Number(counts[userId] || 0);

            if (n > 0) {

                el.classList.remove('hidden');
                el.textContent = n > 99 ? '99+' : String(n);

            } else {

                el.classList.add('hidden');
                el.textContent = '0';
            }
        });
    })

    .catch(err => {
        console.log(err);
    });
}

document.addEventListener('DOMContentLoaded', function () {

    // ✅ pertama load
    refreshUnreadByTenant();

    // ✅ realtime polling
    setInterval(refreshUnreadByTenant, 2000);

    // ✅ saat kembali focus dari room chat
    window.addEventListener('focus', refreshUnreadByTenant);

    // ✅ saat back browser
    window.addEventListener('pageshow', refreshUnreadByTenant);
});
</script>
@endsection