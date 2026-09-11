@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <p class="text-sm text-gray-500 mb-4">Ringkasan kehadiran untuk tanggal: <strong>{{ \Carbon\Carbon::parse($tanggalHariIni)->translatedFormat('d F Y') }}</strong></p>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Total Pegawai</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary['total_karyawan'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Hadir</p>
            <p class="text-2xl font-bold text-green-600">{{ $summary['hadir'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Terlambat</p>
            <p class="text-2xl font-bold text-orange-500">{{ $summary['terlambat'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Izin</p>
            <p class="text-2xl font-bold text-blue-500">{{ $summary['izin'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <p class="text-xs text-gray-500">Sakit</p>
            <p class="text-2xl font-bold text-red-500">{{ $summary['sakit'] }}</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.rekap.harian') }}" class="text-blue-700 text-sm font-medium hover:underline">
            Lihat Rekap Harian Lengkap &rarr;
        </a>
    </div>
@endsection