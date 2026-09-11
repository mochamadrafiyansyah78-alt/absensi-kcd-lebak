<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\HariLibur;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Cuti;

class AbsensiController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::orderBy('nama')->get();

        return view('absen.index', compact('karyawans'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_id'      => 'required|exists:karyawans,id',
            'status_kehadiran' => 'required|in:hadir,izin,sakit',
            'tipe_absensi'     => 'nullable|in:masuk,pulang',
            'foto_base64'      => [
                'required',
                'string',
                'regex:/^data:image\/(jpeg|jpg|png);base64,/',
                function ($attribute, $value, $fail) {
                    $sizeInBytes = (strlen($value) * 3) / 4;
                    if ($sizeInBytes > 5 * 1024 * 1024) {
                        $fail('Ukuran foto tidak boleh lebih dari 5MB.');
                    }
                },
            ],
            'latitude'         => 'required|numeric|between:-90,90',
            'longitude'        => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data    = $validator->validated();
        $now     = Carbon::now('Asia/Jakarta');
        $tanggal = $now->toDateString();

        // ================= BLOKIR HARI LIBUR (Revisi #3) =================
        if (HariLibur::isLibur($tanggal)) {
            $ket = HariLibur::keterangan($tanggal);
            return back()->with('error', 'Hari ini adalah hari libur' . ($ket ? " ({$ket})" : '') . '. Absensi tidak dapat diproses.')->withInput();
        }

                // ================= BLOKIR JIKA SEDANG CUTI =================
        $sedangCuti = Cuti::sedangCuti($data['karyawan_id'], $tanggal);
        if ($sedangCuti) {
            return back()->with('error', 'Anda sedang dalam masa cuti hingga ' . $sedangCuti->tanggal_selesai->translatedFormat('d F Y') . '. Absensi tidak dapat diproses.')->withInput();
        }

        // ================= STATUS IZIN / SAKIT =================
        if (in_array($data['status_kehadiran'], ['izin', 'sakit'])) {
            $existing = Absensi::where('karyawan_id', $data['karyawan_id'])
                ->where('tanggal', $tanggal)
                ->whereNull('tipe_absensi')
                ->first();

            if ($existing) {
                return back()->with('error', 'Anda sudah mengisi absensi untuk hari ini.');
            }

            $fotoPath = $this->simpanFotoBase64($data['foto_base64'], $data['karyawan_id'], $data['status_kehadiran']);

            Absensi::create([
                'karyawan_id'      => $data['karyawan_id'],
                'tanggal'          => $tanggal,
                'status_kehadiran' => $data['status_kehadiran'],
                'tipe_absensi'     => null,
                'jam'              => $now->toTimeString(),
                'status_waktu'     => null,
                'foto_path'        => $fotoPath,
                'latitude'         => $data['latitude'],
                'longitude'        => $data['longitude'],
            ]);

            return back()->with('success', 'Absensi (' . $data['status_kehadiran'] . ') berhasil dikirim.');
        }

        // ================= STATUS HADIR =================
        if (empty($data['tipe_absensi'])) {
            return back()->with('error', 'Tipe absensi (Masuk/Pulang) wajib dipilih untuk status Hadir.')->withInput();
        }

        $statusWaktu = null;

        // Revisi #2: Absen masuk DITUTUP TOTAL setelah pukul 07:45, bukan sekadar ditandai "Terlambat"
        if ($data['tipe_absensi'] === 'masuk') {
            $batasMasuk = Carbon::createFromTimeString('07:45:00', 'Asia/Jakarta')->setDateFrom($now);

            if ($now->gt($batasMasuk)) {
                return back()->with('error', 'Absen masuk sudah ditutup. Batas waktu absen masuk pukul 07:45 WIB.')->withInput();
            }

            $statusWaktu = 'tepat_waktu';
        }

        if ($data['tipe_absensi'] === 'pulang') {
            $hari = $now->dayOfWeekIso;

            if ($hari >= 1 && $hari <= 4) {
                $batasPulang = Carbon::createFromTimeString('16:00:00', 'Asia/Jakarta')->setDateFrom($now);
            } elseif ($hari === 5) {
                $batasPulang = Carbon::createFromTimeString('17:00:00', 'Asia/Jakarta')->setDateFrom($now);
            } else {
                return back()->with('error', 'Absen pulang tidak berlaku pada hari Sabtu/Minggu.')->withInput();
            }

            if ($now->lt($batasPulang)) {
                return back()->with('error', 'Absen pulang belum dapat diproses. Buka mulai pukul ' . $batasPulang->format('H:i') . ' WIB.')->withInput();
            }

            $statusWaktu = null;
        }

        $sudahAbsen = Absensi::where('karyawan_id', $data['karyawan_id'])
            ->where('tanggal', $tanggal)
            ->where('tipe_absensi', $data['tipe_absensi'])
            ->exists();

        if ($sudahAbsen) {
            return back()->with('error', 'Anda sudah melakukan absen ' . $data['tipe_absensi'] . ' hari ini.')->withInput();
        }

        $fotoPath = $this->simpanFotoBase64($data['foto_base64'], $data['karyawan_id'], $data['tipe_absensi']);

        Absensi::create([
            'karyawan_id'      => $data['karyawan_id'],
            'tanggal'          => $tanggal,
            'status_kehadiran' => 'hadir',
            'tipe_absensi'     => $data['tipe_absensi'],
            'jam'              => $now->toTimeString(),
            'status_waktu'     => $statusWaktu,
            'foto_path'        => $fotoPath,
            'latitude'         => $data['latitude'],
            'longitude'        => $data['longitude'],
        ]);

        return back()->with('success', 'Absensi ' . $data['tipe_absensi'] . ' berhasil dikirim.');
    }

    private function simpanFotoBase64(string $base64, int $karyawanId, string $keterangan): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            $extension = 'jpg';
        }

        $imageData = base64_decode($base64);
        $fileName  = 'absensi/' . now()->format('Y-m-d') . '/' . $karyawanId . '_' . $keterangan . '_' . time() . '.' . $extension;

        Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }
}