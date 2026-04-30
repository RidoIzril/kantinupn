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

        </a>
        @empty
        <p class="text-gray-500 text-center py-10">
            Belum ada chat
        </p>
        @endforelse

    </div>
</div>
@endsection