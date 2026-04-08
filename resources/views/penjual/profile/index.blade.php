@extends('layouts.app')
@section('title','Edit Profile Penjual')

@section('content')
<div class="p-6 max-w-3xl">
    <h1 class="text-xl font-bold mb-4">Edit Profile Penjual</h1>

    <div id="alertBox" class="hidden mb-4 text-sm rounded p-3"></div>

    <form id="formEditProfile" class="space-y-4 bg-white p-5 rounded shadow" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <input type="hidden" id="users_id" name="users_id">

        <input id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap"
               class="w-full border p-2 rounded" required>

        <input id="kontak" name="kontak" placeholder="Kontak"
               class="w-full border p-2 rounded">

        <select id="gender" name="gender" class="w-full border p-2 rounded">
    <option value="">-- Pilih Gender --</option>
    <option value="Laki-Laki">Laki-Laki</option>
    <option value="Perempuan">Perempuan</option>
</select>

        <select id="status" name="status" class="w-full border p-2 rounded" required>
            <option value="aktif">aktif</option>
            <option value="nonaktif">nonaktif</option>
        </select>

        <hr>

        <input id="tenant_name" name="tenant_name" placeholder="Nama Tenant"
               class="w-full border p-2 rounded" required>

        <input id="no_tenant" name="no_tenant" placeholder="No Tenant"
               class="w-full border p-2 rounded">

        <input type="file" id="foto_tenant" name="foto_tenant" class="w-full border p-2 rounded">

        <img id="previewFoto" class="w-24 h-24 object-cover rounded hidden" alt="Foto Tenant">

        <div class="flex gap-2">
            <button id="btnSubmit" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                Update
            </button>
            <a href="{{ route('penjual.profile.show') }}" class="px-4 py-2 bg-slate-300 rounded">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token');
    const role  = (localStorage.getItem('role') || '').toLowerCase();

    const alertBox = document.getElementById('alertBox');
    const form = document.getElementById('formEditProfile');
    const btnSubmit = document.getElementById('btnSubmit');
    const previewFoto = document.getElementById('previewFoto');

    const showAlert = (msg, type = 'error') => {
        alertBox.classList.remove('hidden');
        alertBox.className = type === 'success'
            ? 'mb-4 text-sm rounded p-3 bg-green-100 text-green-700'
            : 'mb-4 text-sm rounded p-3 bg-red-100 text-red-700';
        alertBox.textContent = msg;
    };

    if (!token || role !== 'penjual') {
        window.location.replace("{{ url('/login') }}");
        return;
    }

    // 1) Load data profile dari API
    try {
        const res = await fetch("{{ url('/api/penjual/profile') }}", {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        const raw = await res.text();
        let json = {};
        try { json = JSON.parse(raw); } catch (_) {}

        if (!res.ok) {
            if (res.status === 401) {
                localStorage.removeItem('token');
                localStorage.removeItem('role');
                window.location.replace("{{ url('/login') }}");
                return;
            }
            showAlert(`Gagal memuat profile (${res.status})`);
            return;
        }

        const p = json.data || {};
        document.getElementById('users_id').value = p.users_id ?? '';
        document.getElementById('nama_lengkap').value = p.nama_lengkap ?? '';
        document.getElementById('kontak').value = p.kontak ?? '';
        document.getElementById('gender').value = p.gender ?? '';
        document.getElementById('status').value = p.status ?? 'aktif';
        document.getElementById('tenant_name').value = p.tenant?.tenant_name ?? '';
        document.getElementById('no_tenant').value = p.tenant?.no_tenant ?? '';

        if (p.tenant?.foto_tenant) {
            previewFoto.src = `/storage/${p.tenant.foto_tenant}`;
            previewFoto.classList.remove('hidden');
        }
    } catch (e) {
        console.error(e);
        showAlert('Terjadi error saat memuat data profile.');
    }

    // preview gambar baru
    document.getElementById('foto_tenant').addEventListener('change', function () {
        const file = this.files?.[0];
        if (!file) return;
        previewFoto.src = URL.createObjectURL(file);
        previewFoto.classList.remove('hidden');
    });

    // 2) Submit update via API
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Menyimpan...';

        try {
            const fd = new FormData(form);
            fd.append('_method', 'PUT'); // spoof method for Laravel

            const res = await fetch("{{ url('/api/penjual/profile/update') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: fd
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok) {
                if (res.status === 401) {
                    localStorage.removeItem('token');
                    localStorage.removeItem('role');
                    window.location.replace("{{ url('/login') }}");
                    return;
                }
                showAlert(json.message || `Gagal update profile (${res.status})`);
                return;
            }

            showAlert('Profile berhasil diupdate.', 'success');
            setTimeout(() => {
                window.location.href = "{{ route('penjual.profile.show') }}";
            }, 700);

        } catch (err) {
            console.error(err);
            showAlert('Terjadi error jaringan/server saat update profile.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Update';
        }
    });
});
</script>
@endpush