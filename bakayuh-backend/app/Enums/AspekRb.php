<?php

namespace App\Enums;

enum AspekRb: string
{
    case Reform = 'reform';
    case Pemenuhan = 'pemenuhan';
    case Hasil = 'hasil';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::Reform => 'Aspek Reform',
            self::Pemenuhan => 'Aspek Pemenuhan',
            self::Hasil => 'Aspek Hasil',
            self::None => 'Umum',
        };
    }
}
