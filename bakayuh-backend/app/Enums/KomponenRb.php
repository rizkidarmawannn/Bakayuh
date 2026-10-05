<?php

namespace App\Enums;

enum KomponenRb: string
{
    case Pengungkit = 'pengungkit';
    case Hasil = 'hasil';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Pengungkit => 'Komponen Pengungkit',
            self::Hasil => 'Komponen Hasil',
            self::None => 'Tanpa Komponen',
        };
    }
}
