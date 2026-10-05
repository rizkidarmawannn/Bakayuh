<?php

namespace App\Enums;

enum Triwulan: string
{
    case TW1 = 'TW1';
    case TW2 = 'TW2';
    case TW3 = 'TW3';
    case TW4 = 'TW4';

    public function label(): string
    {
        return match ($this) {
            self::TW1 => 'Triwulan I (Jan - Mar)',
            self::TW2 => 'Triwulan II (Apr - Jun)',
            self::TW3 => 'Triwulan III (Jul - Sep)',
            self::TW4 => 'Triwulan IV (Okt - Des)',
        };
    }
}
