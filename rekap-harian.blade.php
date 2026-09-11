@extends('layouts.admin')

@section('title', 'Rekap Harian')
@section('page-title', 'Rekap Harian')

@section('content')

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" class="border rounded px-3 py-2 text-sm">
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-gray-500 mb-1">Cari Nama/NIP</label>
            <input type="text" name="cari" value="{{ $cari }}" placeholder="Ketik nama atau NIP..." class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <button type="submit" class="bg-blue-700 text-white text-sm px-4 py-2 rounded">Filter</button>
        <a href="{{ route('admin.export.harian', ['tanggal' => $tanggal]) }}" class="bg-green-600 text-white text-sm px-4 py-2 rounded">
            UNDUH REKAP HARIAN (.xlsx)
        </a>
    </form>

    {{-- Tabel Rekap --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left">No</th>
                    <th class="px-3 py-2 text-left">Nama Pegawai</th>
                    <th class="px-3 py-2 text-left">NIP</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Jam Masuk</th>
                    <th class="px-3 py-2 text-left">Jam Pulang</th>
                    <th class="px-3 py-2 text-left">Foto</th>
                    <th class="px-3 py-2 text-left">Lokasi GPS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap as $i => $r)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">{{ $r->karyawan->nama }}</td>
                        <td class="px-3 py-2">{{ $r->karyawan->nip }}</td>
                        <td class="px-3 py-2">
                            @if ($r->izin_sakit)
                                <span class="px-2 py-1 rounded text-xs {{ $r->izin_sakit->status_kehadiran === 'izin' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($r->izin_sakit->status_kehadiran) }}
                                </span>
                            @elseif ($r->masuk)
                                <span class="px-2 py-1 rounded text-xs bg-green-100 text-green-700">Hadir</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-gray-100 text-gray-500">Belum Absen</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if ($r->masuk)
                                {{ \Carbon\Carbon::parse($r->masuk->jam)->format('H:i') }}
                                @if ($r->masuk->status_waktu === 'terlambat')
                                    <span class="text-orange-500 text-xs">(Terlambat)</span>
                                @else
                                    <span class="text-green-600 text-xs">(Tepat)</span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            {{ $r->pulang ? \Carbon\Carbon::parse($r->pulang->jam)->format('H:i') : '-' }}
                        </td>
                        <td class="px-3 py-2">
                            @php $fotoRecord = $r->masuk ?? $r->pulang ?? $r->izin_sakit; @endphp
                            @if ($fotoRecord && $fotoRecord->foto_path)
                                <a href="{{ asset('storage/' . $fotoRecord->foto_path) }}" target="_blank" class="text-blue-600 text-xs underline">Lihat</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if ($fotoRecord && $fotoRecord->latitude)
                                <a href="https://www.google.com/maps?q={{ $fotoRecord->latitude }},{{ $fotoRecord->longitude }}" target="_blank" class="text-blue-600 text-xs underline">
                                    Google Maps
                                </a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-center text-gray-400">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection