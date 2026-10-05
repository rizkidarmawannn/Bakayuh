<?php

namespace App\Services;

use App\Models\IndikatorKinerja;
use App\Enums\PolaritasIku;

class PersentaseCapaianService
{
    /**
     * Hitung persentase capaian IKU berdasarkan target, realisasi, dan polaritas.
     *
     * Polaritas Positif (makin tinggi makin baik):
     * Persentase = (Realisasi / Target) * 100%
     *
     * Polaritas Negatif (makin rendah makin baik, misal angka pelanggaran):
     * Persentase = (Target / Realisasi) * 100% (atau (2 - (Realisasi / Target)) * 100%)
     */
    public function hitung(float $target, float $realisasi, PolaritasIku $polaritas): float
    {
        if ($target <= 0) {
            return $realisasi > 0 ? 100.0 : 0.0;
        }

        if ($polaritas === PolaritasIku::Negatif) {
            if ($realisasi <= 0) {
                return 100.0;
            }
            // Rumus standar Kemenkum/KemenPAN-RB untuk polaritas negatif:
            // ((Target - (Realisasi - Target)) / Target) * 100%
            $capaian = ((2 * $target - $realisasi) / $target) * 100;
            return round(max(0, $capaian), 2);
        }

        return round(($realisasi / $target) * 100, 2);
    }
}