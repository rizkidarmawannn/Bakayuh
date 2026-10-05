<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAnggaran;

class TahunAnggaranSeeder extends Seeder
{
    public function run(): void
    {
        TahunAnggaran::firstOrCreate(['tahun' => 2025], ['is_aktif' => false]);
        TahunAnggaran::firstOrCreate(['tahun' => 2026], ['is_aktif' => true]);
    }
}
