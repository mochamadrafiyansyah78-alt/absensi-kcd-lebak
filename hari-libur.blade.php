@extends('layouts.admin')

@section('title', 'Hari Libur')
@section('page-title', 'Kelola Hari Libur Nasional')

@section('content')

    <p class="text-sm text-gray-500 mb-4">
        Hari Sabtu dan Minggu otomatis terdeteksi sebagai hari libur. Gunakan form ini hanya untuk menambahkan tanggal merah/libur nasional lainnya.
    </p>

    <div class="bg-white rounded-lg shadow p-4 mb-4 max-w-lg">
        <form method="POST" action="{{ route('admin.hari-libur.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal') }}" class="border rounded px-3 py-2 text-sm" required>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}" placeholder="Contoh: Hari Kemerdekaan RI" class="w-full border rounded px-3 py-2 text-sm" required>
            </div>
            <button type="submit" class="bg-blue-700 text-white text-sm px-4 py-2 rounded">Tambah</button>
        </form>

        @error('tanggal')
            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto max-w-lg">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left">Tanggal</th>
                    <th class="px-3 py-2 text-left">Keterangan</th>
                    <th class="px-3 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($hariLiburs as $h)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="px-3 py-2">{{ $h->keterangan }}</td>
                        <td class="px-3 py-2 text-center">
                            <form action="{{ route('admin.hari-libur.destroy', $h->id) }}" method="POST"
                                  onsubmit="return confirm('Hapus hari libur ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-3 py-4 text-center text-gray-400">Belum ada data hari libur nasional.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection