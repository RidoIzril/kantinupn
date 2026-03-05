@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-gray-100">

    @include('customer.sidebarcus')

    <div class="flex-1 p-6">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-800">Edit Profile</h1>
        </div>

        <div class="bg-white rounded-xl shadow p-6 max-w-3xl">

            <form method="POST" action="{{ route('customer.profile.update') }}"
                  class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                @method('PUT')

                {{-- Full Name --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name</label>
                    <input type="text" name="customer_fullname"
                           value="{{ old('customer_fullname', $customer->customer_fullname) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="customer_email"
                           value="{{ old('customer_email', $customer->customer_email) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-sm font-medium mb-1">No. Phone</label>
                    <input type="text" name="customer_contact"
                           value="{{ old('customer_contact', $customer->customer_contact) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- DOB --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Lahir</label>
                    <input type="date" name="customer_dob"
                           value="{{ old('customer_dob', $customer->customer_dob) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- Gender --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Gender</label>
                    <select name="customer_gender"
                            class="w-full border rounded-lg px-3 py-2">
                        <option value="Laki-laki" {{ $customer->customer_gender == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ $customer->customer_gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                {{-- Fakultas --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Fakultas</label>
                    <input type="text" name="customer_faculty"
                           value="{{ old('customer_faculty', $customer->customer_faculty) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <input type="text" name="customer_status"
                           value="{{ old('customer_status', $customer->customer_status) }}"
                           class="w-full border rounded-lg px-3 py-2">
                </div>

                {{-- BUTTON --}}
                <div class="md:col-span-2 flex justify-end gap-3 mt-4">
                    <a href="{{ route('profile.profilecustomer') }}"
                       class="px-4 py-2 border rounded-lg">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-green-700 text-white rounded-lg hover:bg-green-800">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
