<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CutiController extends Controller
{
    /**
     * Tampilkan form pengajuan cuti.
     */
    public function create()
    {
        $karyawans = Karyawan::orderBy('nama')->get();

        return view('cuti.create', compact('karyawans'));
    }

    /**
     * Simpan pengajuan cuti baru dengan status pending.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'karyawan_id'      => 'required|exists:karyawans,id',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'       => 'required|string|max:500',
            'foto_bukti'       => [
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
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Cegah pengajuan yang tanggalnya tumpang tindih dengan pengajuan lain
        // yang masih pending atau sudah disetujui.
        $overlap = Cuti::where('karyawan_id', $data['karyawan_id'])
            ->whereIn('status', ['pending', 'disetujui'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('tanggal_mulai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhereBetween('tanggal_selesai', [$data['tanggal_mulai'], $data['tanggal_selesai']])
                  ->orWhere(function ($q2) use ($data) {
                      $q2->where('tanggal_mulai', '<=', $data['tanggal_mulai'])
                         ->where('tanggal_selesai', '>=', $data['tanggal_selesai']);
                  });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'Sudah ada pengajuan cuti (pending/disetujui) yang tumpang tindih pada rentang tanggal tersebut.')->withInput();
        }

        $fotoPath = $this->simpanFotoBase64($data['foto_bukti'], $data['karyawan_id']);

        Cuti::create([
            'karyawan_id'     => $data['karyawan_id'],
            'tanggal_mulai'   => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'keterangan'      => $data['keterangan'],
            'foto_bukti'      => $fotoPath,
            'status'          => 'pending',
        ]);

        return back()->with('success', 'Pengajuan cuti berhasil dikirim. Menunggu verifikasi admin.');
    }

    private function simpanFotoBase64(string $base64, int $karyawanId): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
        } else {
            $extension = 'jpg';
        }

        $imageData = base64_decode($base64);
        $fileName  = 'cuti/' . now()->format('Y-m-d') . '/' . $karyawanId . '_' . time() . '.' . $extension;

        Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }
}