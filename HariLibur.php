<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $fillable = ['tanggal', 'keterangan'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Cek apakah tanggal tertentu adalah hari libur (weekend atau libur nasional).
     */
    public static function isLibur(string|DateTimeInterface $tanggal): bool
    {
        $carbon = Carbon::parse($tanggal);

        if ($carbon->isWeekend()) {
            return true;
        }

        return static::whereDate('tanggal', $carbon->toDateString())->exists();
    }

    /**
     * Ambil keterangan hari libur (Sabtu/Minggu/nama libur nasional).
     */
    public static function keterangan(string|DateTimeInterface $tanggal): ?string
    {
        $carbon = Carbon::parse($tanggal);

        if ($carbon->isSaturday()) {
            return 'Hari Sabtu';
        }

        if ($carbon->isSunday()) {
            return 'Hari Minggu';
        }

        $libur = static::whereDate('tanggal', $carbon->toDateString())->first();

        return $libur?->keterangan;
    }
}