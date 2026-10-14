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
        $kanwil = SatuanKerja::where('kode', 'KANWIL-KALSEL')->first();

        // 1. Admin Account
        // username: admin | password: Kanwil123
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Kanwil',
                'email' => 'admin@kemenkum.go.id',
                'password' => Hash::make('Kanwil123'),
                'role' => UserRole::SuperAdmin,
                'satker_id' => $kanwil?->id,
                'is_active' => true,
            ]
        );

        // 2. 6 Unit Accounts
        // username: ahu, ki, setjen, bsk, bhpn, pp | password: Daduk@2026
        $unitAccounts = [
            'ahu' => 'Administrasi Hukum Umum (AHU)',
            'ki' => 'Kekayaan Intelektual (KI)',
            'setjen' => 'Sekretariat Jenderal (Setjen)',
            'bsk' => 'Badan Strategi Kebijakan (BSK)',
            'bhpn' => 'Badan Pembinaan Hukum Nasional (BHPN)',
            'pp' => 'Peraturan Perundang-undangan (PP)',
        ];

        foreach ($unitAccounts as $uname => $label) {
            User::updateOrCreate(
                ['username' => $uname],
                [
                    'name' => 'Operator ' . $label,
                    'email' => $uname . '@kemenkum.go.id',
                    'password' => Hash::make('Daduk@2026'),
                    'role' => UserRole::OperatorSatker,
                    'satker_id' => $kanwil?->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
