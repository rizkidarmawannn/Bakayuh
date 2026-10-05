<?php

namespace App\Enums;

enum PredikatSakip: string
{
    case AA = 'AA';
    case A = 'A';
    case BB = 'BB';
    case B = 'B';
    case CC = 'CC';
    case C = 'C';
    case D = 'D';

    public function label(): string
    {
        return match ($this) {
            self::AA => 'Sangat Memuaskan',
            self::A => 'Memuaskan',
            self::BB => 'Sangat Baik',
            self::B => 'Baik',
            self::CC => 'Cukup',
            self::C => 'Kurang',
            self::D => 'Sangat Kurang',
        };
    }

    public static function fromNilai(float $nilai): self
    {
        return match (true) {
            $nilai > 90 => self::AA,
            $nilai >= 80 => self::A,
            $nilai >= 70 => self::BB,
            $nilai >= 60 => self::B,
            $nilai >= 50 => self::CC,
            $nilai >= 30 => self::C,
            default => self::D,
        };
    }
}
