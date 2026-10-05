<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SatuanKerjaSeeder::class,
            TahunAnggaranSeeder::class,
            UserSeeder::class,
            IndikatorKinerjaSeeder::class,
            LkeZi2026Seeder::class,
        ]);
    }
}