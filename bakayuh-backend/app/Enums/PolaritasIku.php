<?php

namespace App\Enums;

enum PolaritasIku: string
{
    case Positif = 'positif';
    case Negatif = 'negatif';

    public function label(): string
    {
        return match ($this) {
            self::Positif => 'Positif (Semakin tinggi semakin baik)',
            self::Negatif => 'Negatif (Semakin rendah semakin baik)',
        };
    }
}
