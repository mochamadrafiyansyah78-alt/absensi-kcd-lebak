@extends('layouts.admin')

@section('title', 'Pengajuan Cuti')
@section('page-title', 'Verifikasi Pengajuan Cuti')

@section('content')

    <div class="mb-4 flex gap-2">
        <a href="{{ route('admin.pengajuan-cuti.index', ['status' => 'pending']) }}"
           class="px-3 py-1.5 rounded text-sm {{ $status === 'pending' ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 border' }}">
            Menunggu
        </a>
        <a href="{{ route('admin.pengajuan-cuti.index', ['status' => 'disetujui']) }}"
           class="px-3 py-1.5 rounded text-sm {{ $status === 'disetujui' ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 border' }}">
            Disetujui
        </a>
        <a href="{{ route('admin.pengajuan-cuti.index', ['status' => 'ditolak']) }}"
           class="px-3 py-1.5 rounded text-sm {{ $status === 'ditolak' ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 border' }}">
            Ditolak
        </a>
        <a href="{{ route('admin.pengajuan-cuti.index', ['status' => 'semua']) }}"
           class="px-3 py-1.5 rounded text-sm {{ $status === 'semua' ? 'bg-blue-700 text-white' : 'bg-white text-gray-600 border' }}">
            Semua
        </a>
    </div>

    <div class="space-y-3">
        @forelse ($cutis as $c)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $c->karyawan->nama }}</p>
                        <p class="text-xs text-gray-500">{{ $c->karyawan->nip }} — {{ $c->karyawan->jabatan }}</p>
                        <p class="text-sm mt-2">
                            <span class="font-medium">Periode:</span>
                            {{ $c->tanggal_mulai->translatedFormat('d M Y') }} s/d {{ $c->tanggal_selesai->translatedFormat('d M Y') }}
                            <span class="text-gray-500">({{ $c->tanggal_mulai->diffInDays($c->tanggal_selesai) + 1 }} hari)</span>
                        </p>
                        <p class="text-sm mt-1"><span class="font-medium">Keterangan:</span> {{ $c->keterangan }}</p>
                        @if ($c->catatan_admin)
                            <p class="text-sm mt-1 text-red-600"><span class="font-medium">Catatan Admin:</span> {{ $c->catatan_admin }}</p>
                        @endif
                        <a href="{{ asset('storage/' . $c->foto_bukti) }}" target="_blank" class="text-blue-600 text-xs underline mt-2 inline-block">
                            Lihat Foto Bukti
                        </a>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <span class="px-2 py-1 rounded text-xs font-medium
                            {{ $c->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $c->status === 'disetujui' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $c->status === 'ditolak' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ ucfirst($c->status) }}
                        </span>

                        @if ($c->status === 'pending')
                            <form action="{{ route('admin.pengajuan-cuti.setujui', $c->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white text-xs px-3 py-1.5 rounded">Setujui</button>
                            </form>

                            <button type="button" onclick="document.getElementById('tolak-form-{{ $c->id }}').classList.toggle('hidden')"
                                    class="bg-red-600 text-white text-xs px-3 py-1.5 rounded">
                                Tolak
                            </button>
                        @endif
                    </div>
                </div>

                @if ($c->status === 'pending')
                    <form id="tolak-form-{{ $c->id }}" action="{{ route('admin.pengajuan-cuti.tolak', $c->id) }}" method="POST" class="hidden mt-3 flex gap-2">
                        @csrf
                        <input type="text" name="catatan_admin" placeholder="Alasan penolakan (opsional)" class="flex-1 border rounded px-3 py-1.5 text-sm">
                        <button type="submit" class="bg-red-600 text-white text-xs px-3 py-1.5 rounded">Kirim Penolakan</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-400 text-sm">
                Tidak ada pengajuan cuti pada kategori ini.
            </div>
        @endforelse
    </div>
@endsection