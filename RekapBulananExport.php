<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\Karyawan;
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
use App\Models\Cuti;

class RekapBulananExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithColumnWidths, WithCustomValueBinder
{
    protected int $bulan;
    protected int $tahun;
    protected ?int $karyawanId;

    public function __construct(int $bulan, int $tahun, $karyawanId = null)
    {
        $this->bulan      = $bulan;
        $this->tahun      = $tahun;
        $this->karyawanId = $karyawanId ? (int) $karyawanId : null;
    }

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
        $karyawans = Karyawan::when($this->karyawanId, fn($q) => $q->where('id', $this->karyawanId))
            ->orderBy('nama')
            ->get();

        $absensiBulan = Absensi::whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->get()
            ->groupBy('karyawan_id');

        return $karyawans->map(function ($k) use ($absensiBulan) {
            $records = $absensiBulan->get($k->id, collect());

            return (object) [
                'karyawan'  => $k,
                'hadir'     => $records->where('status_kehadiran', 'hadir')->where('tipe_absensi', 'masuk')->count(),
                'terlambat' => $records->where('status_waktu', 'terlambat')->count(),
                'izin'      => $records->where('status_kehadiran', 'izin')->count(),
                'sakit'     => $records->where('status_kehadiran', 'sakit')->count(),
                'cuti'      => Cuti::totalHariCutiDalamBulan($k->id, $this->bulan, $this->tahun),
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'NIP', 'Nama Pegawai', 'Jabatan', 'Total Hadir', 'Total Terlambat', 'Total Izin', 'Total Sakit', 'Total Cuti'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->karyawan->nip,
            $row->karyawan->nama,
            $row->karyawan->jabatan,
            $row->hadir,
            $row->terlambat,
            $row->izin,
            $row->sakit,
            $row->cuti,
        ];
    }

    public function title(): string
    {
        return 'Rekap Bulanan ' . $this->bulan . '-' . $this->tahun;
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
            'F' => 16,
            'G' => 12,
            'H' => 12,
            'I' => 12,
        ];
    }
}