@extends('layouts.admin')

@section('title', 'Rekap Bulanan')
@section('page-title', 'Rekap Bulanan')

@section('content')

    <form method="GET" class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Bulan</label>
            <select name="bulan" class="border rounded px-3 py-2 text-sm">
                @foreach (range(1, 12) as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="border rounded px-3 py-2 text-sm">
                @foreach (range(now()->year - 2, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-gray-500 mb-1">Nama Pegawai</label>
            <select name="karyawan_id" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">-- Semua Pegawai --</option>
                @foreach ($semuaKaryawan as $k)
                    <option value="{{ $k->id }}" {{ (string) $karyawanId === (string) $k->id ? 'selected' : '' }}>
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-700 text-white text-sm px-4 py-2 rounded">Filter</button>
        <a href="{{ route('admin.export.bulanan', ['bulan' => $bulan, 'tahun' => $tahun, 'karyawan_id' => $karyawanId]) }}" class="bg-green-600 text-white text-sm px-4 py-2 rounded">
            UNDUH REKAP BULANAN (.xlsx)
        </a>
    </form>


        <div class="bg-white rounded-lg shadow p-4 text-center">
              <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Hadir</p>
            <p class="text-2xl font-bold text-green-600">{{ $rekap->sum('hadir') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Terlambat</p>
            <p class="text-2xl font-bold text-orange-500">{{ $rekap->sum('terlambat') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Izin</p>
            <p class="text-2xl font-bold text-blue-500">{{ $rekap->sum('izin') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Sakit</p>
            <p class="text-2xl font-bold text-red-500">{{ $rekap->sum('sakit') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Cuti</p>
            <p class="text-2xl font-bold text-purple-600">{{ $rekap->sum('cuti') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-3 py-2 text-left">No</th>
                    <th class="px-3 py-2 text-left">Nama Pegawai</th>
                    <th class="px-3 py-2 text-left">NIP</th>
                    <th class="px-3 py-2 text-left">Jabatan</th>
                    <th class="px-3 py-2 text-center">Total Hadir</th>
                    <th class="px-3 py-2 text-center">Total Terlambat</th>
                    <th class="px-3 py-2 text-center">Total Izin</th>
                    <th class="px-3 py-2 text-center">Total Sakit</th>
                    <th class="px-3 py-2 text-center">Total Cuti</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rekap as $i => $r)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $i + 1 }}</td>
                        <td class="px-3 py-2">{{ $r->karyawan->nama }}</td>
                        <td class="px-3 py-2">{{ $r->karyawan->nip }}</td>
                        <td class="px-3 py-2">{{ $r->karyawan->jabatan }}</td>
                        <td class="px-3 py-2 text-center text-green-600 font-medium">{{ $r->hadir }}</td>
                        <td class="px-3 py-2 text-center text-orange-500 font-medium">{{ $r->terlambat }}</td>
                        <td class="px-3 py-2 text-center text-blue-500 font-medium">{{ $r->izin }}</td>
                        <td class="px-3 py-2 text-center text-red-500 font-medium">{{ $r->sakit }}</td>
                        <td class="px-3 py-2 text-center text-purple-600 font-medium">{{ $r->cuti }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-center text-gray-400">Tidak ada data untuk periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection