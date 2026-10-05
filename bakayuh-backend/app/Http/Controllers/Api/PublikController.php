<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Models\RealisasiIku;
use App\Models\EvaluasiSakip;

class PublikController extends Controller
{
    public function summary(Request $request)
    {
        $tahun = $request->input('tahun');
        $tahunModel = $tahun 
            ? TahunAnggaran::where('tahun', $tahun)->first() 
            : TahunAnggaran::getAktif();

        $tahunId = $tahunModel?->id;

        $totalSatker = SatuanKerja::where('is_active', true)->count();
        $avgIku = RealisasiIku::whereHas('targetIku', fn ($q) => $q->where('tahun_anggaran_id', $tahunId))
            ->avg('persentase_capaian');
        $avgSakip = EvaluasiSakip::where('tahun_anggaran_id', $tahunId)
            ->avg('nilai_total');

        return response()->json([
            'data' => [
                'tahun' => $tahunModel?->tahun,
                'total_satker' => $totalSatker,
                'rata_rata_capaian_iku' => round((float)$avgIku, 2),
                'nilai_sakip_rerata' => round((float)$avgSakip, 2),
                'status_zi' => 'Menuju WBK / WBBM 2026',
            ],
        ]);
    }
}