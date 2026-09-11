@extends('layouts.admin')

@section('title', 'Master Karyawan')
@section('page-title', 'Master Karyawan')

@section('content')

    <div class="flex justify-between items-center mb-4">
        <p class="text-sm text-gray-500">Total {{ $karyawans->count() }} pegawai terdaftar.</p>
        <a href="{{ route('admin.master.karyawan.create') }}" class="bg-blue-700 text-white text-sm px-4 py-2 rounded">
            + Tambah Pegawai
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left">No</th>
                    <th class="px-3 py-2 text-left">NIP</th>
                    <th class="px-3 py-2 text-left">Nama</th>
                    <th class="px-3 py-2 text-left">Jabatan</th>
                    <th class="px-3 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($karyawans as $i => $k)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">{{ $k->nip }}</td>
                        <td class="px-3 py-2">{{ $k->nama }}</td>
                        <td class="px-3 py-2">{{ $k->jabatan }}</td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('admin.master.karyawan.edit', $k->id) }}" class="text-blue-600 text-xs underline mr-3">Edit</a>

                            <form action="{{ route('admin.master.karyawan.destroy', $k->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data {{ $k->nama }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-gray-400">Belum ada data karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection