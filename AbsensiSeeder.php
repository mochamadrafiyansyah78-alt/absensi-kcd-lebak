<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;

class AbsensiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nip' => '198712052025211146', 'nama' => 'Angga Delirisyandi, A.Md. Kep', 'jabatan' => 'Pengelola Layanan Operasional'],
            ['nip' => '198805222025211125', 'nama' => 'Amri Bachari, A.Md. Kep', 'jabatan' => 'Pengelola Layanan Operasional'],
            ['nip' => '199310122025212139', 'nama' => 'Yeny Herliyani, A.Md. Keb', 'jabatan' => 'Pengelola Layanan Operasional'],
            ['nip' => '199208232025212102', 'nama' => 'Risma Rahayu', 'jabatan' => 'Operator Layanan Operasional'],
            ['nip' => '197710052025211063', 'nama' => 'Mulyadi', 'jabatan' => 'Operator Layanan Operasional'],
            ['nip' => '198005042025211128', 'nama' => 'Ahmad Yoyo Jarnuji', 'jabatan' => 'Operator Layanan Operasional'],
            ['nip' => '198307012025211078', 'nama' => 'Erpa Purwandi', 'jabatan' => 'Operator Layanan Operasional'],
            ['nip' => '198909262025211077', 'nama' => 'Dadan Maedan Fahmy', 'jabatan' => 'Operator Layanan Operasional'],
            ['nip' => '198004192025211059', 'nama' => 'Hubaesi', 'jabatan' => 'Pengelola Umum Operasional'],
        ];

        foreach ($data as $item) {
            Karyawan::updateOrCreate(['nip' => $item['nip']], $item);
        }
    }
}