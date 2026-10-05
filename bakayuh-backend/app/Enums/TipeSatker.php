<?php

namespace App\Enums;

enum TipeSatker: string
{
    case Kanwil = 'kanwil';
    case Upt = 'upt';
    case Satker = 'satker';

    public function label(): string
    {
        return match ($this) {
            self::Kanwil => 'Kantor Wilayah',
            self::Upt => 'Unit Pelaksana Teknis',
            self::Satker => 'Satuan Kerja',
        };
    }
}
