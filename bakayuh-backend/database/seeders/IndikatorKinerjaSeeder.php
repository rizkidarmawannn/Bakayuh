<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IndikatorKinerja;
use App\Enums\PolaritasIku;
use App\Enums\LevelIku;

class IndikatorKinerjaSeeder extends Seeder
{
    public function run(): void
    {
        $ikus = [
            [
                'kode' => 'IKU-01',
                'nama' => 'Persentase Satuan Kerja Berpredikat WBK/WBBM',
                'satuan' => '%',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Strategis,
            ],
            [
                'kode' => 'IKU-02',
                'nama' => 'Nilai Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP)',
                'satuan' => 'Poin',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Strategis,
            ],
            [
                'kode' => 'IKU-03',
                'nama' => 'Indeks Kepuasan Masyarakat (IKM) Pelayanan Publik',
                'satuan' => 'Skala 1-4',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Program,
            ],
            [
                'kode' => 'IKU-04',
                'nama' => 'Indeks Persepsi Korupsi (IPK) di Lingkungan Satker',
                'satuan' => 'Skala 1-4',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Program,
            ],
            [
                'kode' => 'IKU-05',
                'nama' => 'Tingkat Kepatuhan Pelaporan LHKPN dan LHKASN Pegawai',
                'satuan' => '%',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Kegiatan,
            ],
            [
                'kode' => 'IKU-06',
                'nama' => 'Persentase Realisasi Anggaran Sesuai Target Triwulanan',
                'satuan' => '%',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Kegiatan,
            ],
            [
                'kode' => 'IKU-07',
                'nama' => 'Tingkat Penyelesaian Tindak Lanjut Temuan Pengawasan Internal (Itjen/BPK)',
                'satuan' => '%',
                'polaritas' => PolaritasIku::Positif,
                'level' => LevelIku::Kegiatan,
            ],
        ];

        foreach ($ikus as $iku) {
            IndikatorKinerja::firstOrCreate(['kode' => $iku['kode']], $iku);
        }
    }
}
