@extends('layouts.admin')

@section('title', $karyawan ? 'Edit Karyawan' : 'Tambah Karyawan')
@section('page-title', $karyawan ? 'Edit Karyawan' : 'Tambah Karyawan')

@section('content')

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $karyawan ? route('admin.master.karyawan.update', $karyawan->id) : route('admin.master.karyawan.store') }}">
            @csrf
            @if ($karyawan)
                @method('PUT')
            @endif

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                <input type="text" name="nip" value="{{ old('nip', $karyawan->nip ?? '') }}"
                       class="w-full border rounded px-3 py-2" required maxlength="20">
            </div>

            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" value="{{ old('nama', $karyawan->nama ?? '') }}"
                       class="w-full border rounded px-3 py-2" required maxlength="100">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" value="{{ old('jabatan', $karyawan->jabatan ?? '') }}"
                       class="w-full border rounded px-3 py-2" required maxlength="100">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-700 text-white text-sm px-4 py-2 rounded">
                    {{ $karyawan ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
                </button>
                <a href="{{ route('admin.master.karyawan') }}" class="bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection