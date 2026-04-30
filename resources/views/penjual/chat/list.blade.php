@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto mt-6 bg-white shadow rounded-xl p-4">

    <h2 class="text-lg font-bold mb-4">Live Chat</h2>

    <div class="space-y-2">

        @forelse($listUser as $user)
            <a href="{{ route('penjual.chat.room', $user->users_id) }}?token={{ request('token') }}"
               class="flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-gray-100 transition-all">

                <div class="flex items-center gap-3">

                    <!-- AVATAR -->
                    <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 shadow">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->nama_lengkap) }}"
                             class="w-full h-full object-cover">
                    </div>

                    <!-- INFO -->
                    <div>
                        <div class="font-semibold text-gray-800">
                            {{ $user->nama_lengkap }}
                        </div>

                        <div class="text-sm text-gray-500">
                            {{ $user->email }}
                        </div>
                    </div>

                </div>

                <div class="flex items-center gap-2">

    {{-- 🔥 BADGE UNREAD --}}
    @if(isset($user->unread) && $user->unread > 0)
        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
            {{ $user->unread }}
        </span>
    @endif

    {{-- ICON --}}
    <div class="text-gray-400">
        ➤
    </div>

</div>

            </a>

        @empty
            <p class="text-gray-500 text-center py-10">
                Belum ada chat
            </p>
        @endforelse

    </div>

</div>
<script>
setInterval(() => {
    location.reload();
}, 1000); // reload tiap 3 detik
</script>
@endsection