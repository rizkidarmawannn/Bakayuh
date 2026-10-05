<?php

namespace App\Enums;

enum KategoriRb: string
{
    case LkeWbkWbbm = 'lke_wbk_wbbm';
    case RktGeneral = 'rkt_general';
    case RktTematik = 'rkt_tematik';
    case RktMeso = 'rkt_meso';

    public function label(): string
    {
        return match ($this) {
            self::LkeWbkWbbm => 'LKE Zona Integritas (WBK/WBBM)',
            self::RktGeneral => 'RKT RB General',
            self::RktTematik => 'RKT RB Tematik',
            self::RktMeso => 'RKT RB Meso',
        };
    }
}
