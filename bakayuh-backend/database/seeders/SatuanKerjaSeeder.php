<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatuanKerja;
use App\Enums\TipeSatker;

class SatuanKerjaSeeder extends Seeder
{
    public function run(): void
    {
        $satkers = [
            ['kode' => 'KANWIL-KALSEN', 'nama' => 'Kantor Wilayah Kemenkum Kalimantan Selatan', 'tipe' => TipeSatker::Kanwil],
            ['kode' => 'LP-BJM', 'nama' => 'Lapas Kelas IIA Banjarmasin', 'tipe' => TipeSatker::Upt],
            ['kode' => 'LP-NKR-BJM', 'nama' => 'Lapas Narkotika Kelas IIA Karang Intan', 'tipe' => TipeSatker::Upt],
            ['kode' => 'LP-KOTABARU', 'nama' => 'Lapas Kelas IIA Kotabaru', 'tipe' => TipeSatker::Upt],
            ['kode' => 'LP-AMUNTAI', 'nama' => 'Lapas Kelas IIB Amuntai', 'tipe' => TipeSatker::Upt],
            ['kode' => 'LP-TANJUNG', 'nama' => 'Lapas Kelas IIB Tanjung', 'tipe' => TipeSatker::Upt],
            ['kode' => 'RT-BARABAI', 'nama' => 'Rutan Kelas IIB Barabai', 'tipe' => TipeSatker::Upt],
            ['kode' => 'RT-KANDANGAN', 'nama' => 'Rutan Kelas IIB Kandangan', 'tipe' => TipeSatker::Upt],
            ['kode' => 'RT-RANTAU', 'nama' => 'Rutan Kelas IIB Rantau', 'tipe' => TipeSatker::Upt],
            ['kode' => 'RT-MARABAHAN', 'nama' => 'Rutan Kelas IIB Marabahan', 'tipe' => TipeSatker::Upt],
            ['kode' => 'RT-PELAIHARI', 'nama' => 'Rutan Kelas IIB Pelaihari', 'tipe' => TipeSatker::Upt],
            ['kode' => 'KANIM-BJM', 'nama' => 'Kantor Imigrasi Kelas I TPI Banjarmasin', 'tipe' => TipeSatker::Upt],
            ['kode' => 'KANIM-BATULICIN', 'nama' => 'Kantor Imigrasi Kelas II TPI Batulicin', 'tipe' => TipeSatker::Upt],
            ['kode' => 'BAPAS-BJM', 'nama' => 'Bapas Kelas I Banjarmasin', 'tipe' => TipeSatker::Upt],
            ['kode' => 'BAPAS-AMUNTAI', 'nama' => 'Bapas Kelas II Amuntai', 'tipe' => TipeSatker::Upt],
            ['kode' => 'RUPBASAN-BJM', 'nama' => 'Rupbasan Kelas I Banjarmasin', 'tipe' => TipeSatker::Upt],
            ['kode' => 'BHP-BJM', 'nama' => 'Balai Harta Peninggalan Banjarmasin', 'tipe' => TipeSatker::Upt],
        ];

        foreach ($satkers as $item) {
            SatuanKerja::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
