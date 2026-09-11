<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapHarianExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithCustomValueBinder
{
    protected string $tanggal;

    public function __construct(string $tanggal)
    {
        $this->tanggal = $tanggal;
    }

    /**
     * Paksa kolom NIP (dan kolom lain yang perlu) selalu dibaca sebagai teks,
     * agar Excel tidak mengubahnya ke notasi ilmiah (misal 1,98E+17).
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function collection()
    {
        $karyawans = Karyawan::orderBy('nama')->get();
        $absensiTanggal = Absensi::where('tanggal', $this->tanggal)->get()->groupBy('karyawan_id');

        return $karyawans->map(function ($k) use ($absensiTanggal) {
            $records = $absensiTanggal->get($k->id, collect());

            return (object) [
                'karyawan'   => $k,
                'masuk'      => $records->firstWhere('tipe_absensi', 'masuk'),
                'pulang'     => $records->firstWhere('tipe_absensi', 'pulang'),
                'izin_sakit' => $records->first(fn($r) => in_array($r->status_kehadiran, ['izin', 'sakit'])),
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'NIP', 'Nama Pegawai', 'Jabatan', 'Status', 'Jam Masuk', 'Keterangan Masuk', 'Jam Pulang', 'Koordinat GPS'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $status = 'Belum Absen';
        if ($row->izin_sakit) {
            $status = ucfirst($row->izin_sakit->status_kehadiran);
        } elseif ($row->masuk) {
            $status = 'Hadir';
        }

        $fotoRecord = $row->masuk ?? $row->pulang ?? $row->izin_sakit;
        $koordinat = $fotoRecord && $fotoRecord->latitude
            ? $fotoRecord->latitude . ', ' . $fotoRecord->longitude
            : '-';

        return [
            $no,
            $row->karyawan->nip,
            $row->karyawan->nama,
            $row->karyawan->jabatan,
            $status,
            $row->masuk ? Carbon::parse($row->masuk->jam)->format('H:i') : '-',
            $row->masuk ? ($row->masuk->status_waktu === 'terlambat' ? 'Terlambat' : 'Tepat Waktu') : '-',
            $row->pulang ? Carbon::parse($row->pulang->jam)->format('H:i') : '-',
            $koordinat,
        ];
    }

    public function title(): string
    {
        return 'Rekap Harian ' . $this->tanggal;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 22,
            'C' => 28,
            'D' => 28,
            'E' => 14,
            'F' => 12,
            'G' => 16,
            'H' => 12,
            'I' => 22,
        ];
    }
}