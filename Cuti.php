<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan',
        'foto_bukti',
        'status',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Cek apakah karyawan sedang dalam masa cuti yang disetujui pada tanggal tertentu.
     */
    public static function sedangCuti(int $karyawanId, string $tanggal): ?self
    {
        return static::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();
    }

    /**
     * Hitung jumlah hari cuti (disetujui) milik karyawan yang overlap dengan bulan/tahun tertentu.
     */
    public static function totalHariCutiDalamBulan(int $karyawanId, int $bulan, int $tahun): int
    {
        $awalBulan  = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $cutis = static::where('karyawan_id', $karyawanId)
            ->where('status', 'disetujui')
            ->where('tanggal_mulai', '<=', $akhirBulan)
            ->where('tanggal_selesai', '>=', $awalBulan)
            ->get();

        $totalHari = 0;

        foreach ($cutis as $c) {
            $mulaiEfektif   = $c->tanggal_mulai->greaterThan($awalBulan) ? $c->tanggal_mulai : $awalBulan;
            $selesaiEfektif = $c->tanggal_selesai->lessThan($akhirBulan) ? $c->tanggal_selesai : $akhirBulan;
            $totalHari += $mulaiEfektif->diffInDays($selesaiEfektif) + 1;
        }

        return $totalHari;
    }
}