<?php

namespace App\Enums;

enum PeriodeRb: string
{
    case B03 = 'B03';
    case B06 = 'B06';
    case B09 = 'B09';
    case B12 = 'B12';

    public function bulan(): string
    {
        return match ($this) {
            self::B03 => 'Maret (B03)',
            self::B06 => 'Juni (B06)',
            self::B09 => 'September (B09)',
            self::B12 => 'Desember (B12)',
        };
    }
}
