<?php

namespace App\Http\Controllers;

use App\Exports\RekapBulananExport;
use App\Exports\RekapHarianExport;
use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Cuti;

class AdminController extends Controller
{
    public function index()
    {
        $tanggalHariIni = Carbon::now('Asia/Jakarta')->toDateString();
        $totalKaryawan  = Karyawan::count();

        $absensiHariIni = Absensi::where('tanggal', $tanggalHariIni)
            ->whereNull('tipe_absensi')
            ->orWhere(function ($q) use ($tanggalHariIni) {
                $q->where('tanggal', $tanggalHariIni)->where('tipe_absensi', 'masuk');
            })
            ->get();

        $summary = [
            'total_karyawan' => $totalKaryawan,
            'hadir'          => $absensiHariIni->where('status_kehadiran', 'hadir')->count(),
            'terlambat'      => $absensiHariIni->where('status_waktu', 'terlambat')->count(),
            'izin'           => $absensiHariIni->where('status_kehadiran', 'izin')->count(),
            'sakit'          => $absensiHariIni->where('status_kehadiran', 'sakit')->count(),
        ];

        return view('admin.dashboard', compact('summary', 'tanggalHariIni'));
    }

    /**
     * Rekap Harian Real-time. Revisi #3: menampilkan info Hari Kerja / Hari Libur.
     */
        public function rekapHarian(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::now('Asia/Jakarta')->toDateString());
        $cari    = $request->input('cari');

        $isLibur         = HariLibur::isLibur($tanggal);
        $keteranganLibur = $isLibur ? HariLibur::keterangan($tanggal) : null;

        $karyawans = Karyawan::when($cari, function ($q) use ($cari) {
                $q->where('nama', 'like', "%{$cari}%")
                  ->orWhere('nip', 'like', "%{$cari}%");
            })
            ->orderBy('nama')
            ->get();

        $absensiTanggal = Absensi::where('tanggal', $tanggal)->get()->groupBy('karyawan_id');

        $rekap = $karyawans->map(function ($k) use ($absensiTanggal, $tanggal) {
            $records = $absensiTanggal->get($k->id, collect());

            return (object) [
                'karyawan'   => $k,
                'masuk'      => $records->firstWhere('tipe_absensi', 'masuk'),
                'pulang'     => $records->firstWhere('tipe_absensi', 'pulang'),
                'izin_sakit' => $records->first(fn($r) => in_array($r->status_kehadiran, ['izin', 'sakit'])),
                'cuti'       => Cuti::sedangCuti($k->id, $tanggal),
            ];
        });

        return view('admin.rekap-harian', compact('rekap', 'tanggal', 'cari', 'isLibur', 'keteranganLibur'));
    }

    /**
     * Rekap Bulanan. Revisi #1: tambah filter per nama pegawai.
     */
        public function rekapBulanan(Request $request)
    {
        $bulan      = (int) $request->input('bulan', Carbon::now('Asia/Jakarta')->month);
        $tahun      = (int) $request->input('tahun', Carbon::now('Asia/Jakarta')->year);
        $karyawanId = $request->input('karyawan_id');

        $semuaKaryawan = Karyawan::orderBy('nama')->get();

        $karyawans = Karyawan::when($karyawanId, fn($q) => $q->where('id', $karyawanId))
            ->orderBy('nama')
            ->get();

        $absensiBulan = Absensi::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get()
            ->groupBy('karyawan_id');

        $rekap = $karyawans->map(function ($k) use ($absensiBulan, $bulan, $tahun) {
            $records = $absensiBulan->get($k->id, collect());

            return (object) [
                'karyawan'  => $k,
                'hadir'     => $records->where('status_kehadiran', 'hadir')->where('tipe_absensi', 'masuk')->count(),
                'terlambat' => $records->where('status_waktu', 'terlambat')->count(),
                'izin'      => $records->where('status_kehadiran', 'izin')->count(),
                'sakit'     => $records->where('status_kehadiran', 'sakit')->count(),
                'cuti'      => Cuti::totalHariCutiDalamBulan($k->id, $bulan, $tahun),
            ];
        });

        return view('admin.rekap-bulanan', compact('rekap', 'bulan', 'tahun', 'karyawanId', 'semuaKaryawan'));
    }
    
    public function exportHarian(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::now('Asia/Jakarta')->toDateString());

        return Excel::download(
            new RekapHarianExport($tanggal),
            'rekap-harian-' . $tanggal . '.xlsx'
        );
    }

    public function exportBulanan(Request $request)
    {
        $bulan      = (int) $request->input('bulan', Carbon::now('Asia/Jakarta')->month);
        $tahun      = (int) $request->input('tahun', Carbon::now('Asia/Jakarta')->year);
        $karyawanId = $request->input('karyawan_id');

        return Excel::download(
            new RekapBulananExport($bulan, $tahun, $karyawanId),
            'rekap-bulanan-' . $bulan . '-' . $tahun . '.xlsx'
        );
    }
}