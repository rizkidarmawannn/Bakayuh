<?php

namespace App\Enums;

enum LevelIku: string
{
    case Strategis = 'strategis';
    case Program = 'program';
    case Kegiatan = 'kegiatan';

    public function label(): string
    {
        return match ($this) {
            self::Strategis => 'Sasaran Strategis',
            self::Program => 'Program',
            self::Kegiatan => 'Kegiatan',
        };
    }
}
