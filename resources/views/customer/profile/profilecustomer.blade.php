@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">

    {{-- SIDEBAR --}}
    @include('customer.sidebarcus')

    {{-- MAIN --}}
    <div class="flex-1 p-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold text-gray-800">Account Profile</h1>

    <a href="{{ route('profile.edit_profilecust') }}"
       class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800 transition">
        ✏️ Edit Profile
    </a>
    </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- PROFILE CARD --}}
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow p-6 text-center">
                    <img src="{{ asset('template/dist/assets/compiled/jpg/1.jpg') }}"
                         alt="Avatar"
                         class="w-28 h-28 rounded-full mx-auto object-cover">

                    <h2 class="mt-4 font-semibold text-lg text-green-700">
                        {{ $customer->customer_username }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ $customer->customer_status }}
                    </p>
                </div>
            </div>

            {{-- DETAIL --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl shadow p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Username --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Username
                            </label>
                            <input type="text" readonly
                                   value="{{ $customer->customer_username }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- Full Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Full Name
                            </label>
                            <input type="text" readonly
                                   value="{{ $customer->customer_fullname }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Email
                            </label>
                            <input type="email" readonly
                                   value="{{ $customer->customer_email }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- No. Phone --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                No. Phone
                            </label>
                            <input type="text" readonly
                                   value="{{ $customer->customer_contact }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- Tanggal Lahir --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Tanggal Lahir
                            </label>
                            <input type="date" readonly
                                   value="{{ $customer->customer_dob }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- Gender --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Gender
                            </label>
                            <input type="text" readonly
                                   value="{{ $customer->customer_gender }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- Fakultas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Fakultas
                            </label>
                            <input type="text" readonly
                                   value="{{ $customer->customer_faculty }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Status
                            </label>
                            <input type="text" readonly
                                   value="{{ $customer->customer_status }}"
                                   class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
