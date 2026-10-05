<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SatuanKerja;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kanwil = SatuanKerja::where('kode', 'KANWIL-KALSEN')->first();
        $lapasBjm = SatuanKerja::where('kode', 'LP-BJM')->first();

        // 1. Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@kemenkum.go.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'role' => UserRole::SuperAdmin,
                'satker_id' => $kanwil?->id,
                'is_active' => true,
            ]
        );

        // 2. Admin Kanwil (Verifikator)
        User::firstOrCreate(
            ['email' => 'verifikator@kemenkum.go.id'],
            [
                'name' => 'Tim Pengelola Kinerja & Verifikator RB',
                'password' => Hash::make('password123'),
                'role' => UserRole::AdminKanwil,
                'satker_id' => $kanwil?->id,
                'is_active' => true,
            ]
        );

        // 3. Operator Satker (Lapas Banjarmasin)
        User::firstOrCreate(
            ['email' => 'operator.lpbjm@kemenkum.go.id'],
            [
                'name' => 'Operator Pokja Lapas Banjarmasin',
                'password' => Hash::make('password123'),
                'role' => UserRole::OperatorSatker,
                'satker_id' => $lapasBjm?->id,
                'is_active' => true,
            ]
        );

        // 4. Viewer / Pimpinan
        User::firstOrCreate(
            ['email' => 'pimpinan@kemenkum.go.id'],
            [
                'name' => 'Kepala Kantor Wilayah',
                'password' => Hash::make('password123'),
                'role' => UserRole::Viewer,
                'satker_id' => $kanwil?->id,
                'is_active' => true,
            ]
        );
    }
}
