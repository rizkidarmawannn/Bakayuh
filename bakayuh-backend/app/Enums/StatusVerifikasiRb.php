<?php

namespace App\Enums;

enum StatusVerifikasiRb: string
{
    case BelumUpload = 'belum_upload';
    case BelumVerif = 'belum_verif';
    case Lengkap = 'lengkap';
    case PerluPerbaikan = 'perlu_perbaikan';
    case Tercapai = 'tercapai';

    public function label(): string
    {
        return match ($this) {
            self::BelumUpload => 'Belum Upload',
            self::BelumVerif => 'Menunggu Verifikasi',
            self::Lengkap => 'Lengkap',
            self::PerluPerbaikan => 'Perlu Perbaikan',
            self::Tercapai => 'Tercapai',
        };
    }

    public function isLocked(): bool
    {
        return in_array($this, [self::Lengkap, self::Tercapai]);
    }

    public function color(): string
    {
        return match ($this) {
            self::BelumUpload => 'gray',
            self::BelumVerif => 'yellow',
            self::Lengkap => 'green',
            self::PerluPerbaikan => 'red',
            self::Tercapai => 'blue',
        };
    }
}
