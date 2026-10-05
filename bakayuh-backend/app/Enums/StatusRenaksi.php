<?php

namespace App\Enums;

enum StatusRenaksi: string
{
    case BelumLapor = 'belum_lapor';
    case MenungguVerifikasi = 'menunggu_verifikasi';
    case Terverifikasi = 'terverifikasi';
    case PerluPerbaikan = 'perlu_perbaikan';

    public function label(): string
    {
        return match ($this) {
            self::BelumLapor => 'Belum Lapor',
            self::MenungguVerifikasi => 'Menunggu Verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::PerluPerbaikan => 'Perlu Perbaikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BelumLapor => 'gray',
            self::MenungguVerifikasi => 'yellow',
            self::Terverifikasi => 'green',
            self::PerluPerbaikan => 'red',
        };
    }
}
