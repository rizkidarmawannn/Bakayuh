<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Models\TargetIku;
use App\Models\RealisasiIku;
use App\Models\RencanaAksi;
use App\Models\RealisasiRenaksi;
use App\Models\EvaluasiSakip;
use App\Models\RbTargetPeriode;
use App\Enums\StatusRenaksi;
use App\Enums\StatusVerifikasiRb;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);

        $totalSatker = SatuanKerja::where('is_active', true)->count();

        // Rata-rata IKU
        $avgIku = RealisasiIku::whereHas('targetIku', fn ($q) => $q->where('tahun_anggaran_id', $tahunId))
            ->avg('persentase_capaian');

        // Renaksi
        $renaksiTotal = RencanaAksi::where('tahun_anggaran_id', $tahunId)->count();
        $renaksiSelesai = RealisasiRenaksi::whereHas('rencanaAksi', fn ($q) => $q->where('tahun_anggaran_id', $tahunId))
            ->where('status', StatusRenaksi::Terverifikasi)
            ->count();

        // SAKIP Tertinggi / Terendah
        $sakipTertinggi = EvaluasiSakip::with('satker')
            ->where('tahun_anggaran_id', $tahunId)
            ->orderBy('nilai_total', 'desc')
            ->first();

        $sakipTerendah = EvaluasiSakip::with('satker')
            ->where('tahun_anggaran_id', $tahunId)
            ->orderBy('nilai_total', 'asc')
            ->first();

        // LKE Pemenuhan Progress
        $lkeTargets = RbTargetPeriode::where('tahun_anggaran_id', $tahunId)->count();
        $lkeFulfilled = RbTargetPeriode::where('tahun_anggaran_id', $tahunId)
            ->whereIn('status_verifikasi', [StatusVerifikasiRb::Lengkap, StatusVerifikasiRb::Tercapai])
            ->count();

        $lkePct = $lkeTargets > 0 ? round(($lkeFulfilled / $lkeTargets) * 100, 1) : 0;

        return response()->json([
            'data' => [
                'total_satker_aktif' => $totalSatker,
                'rata_rata_capaian_iku' => round((float)$avgIku, 2),
                'renaksi_total' => $renaksiTotal,
                'renaksi_selesai' => $renaksiSelesai,
                'sakip_tertinggi' => $sakipTertinggi ? [
                    'nilai' => (float)$sakipTertinggi->nilai_total,
                    'satker' => $sakipTertinggi->satker->nama,
                    'predikat' => $sakipTertinggi->predikat->value,
                ] : null,
                'sakip_terendah' => $sakipTerendah ? [
                    'nilai' => (float)$sakipTerendah->nilai_total,
                    'satker' => $sakipTerendah->satker->nama,
                    'predikat' => $sakipTerendah->predikat->value,
                ] : null,
                'lke_progress' => [
                    'fulfilled' => $lkeFulfilled,
                    'total' => $lkeTargets,
                    'percentage' => $lkePct,
                ],
                'rkt_progress' => [
                    'fulfilled' => 0,
                    'total' => 0,
                    'percentage' => 0,
                ],
            ],
        ]);
    }

    /**
     * Chart Analytics data for visual Dashboard
     */
    public function charts(Request $request)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);

        // 1. SAKIP 4 Components Average Radar Data
        $sakipEvals = EvaluasiSakip::where('tahun_anggaran_id', $tahunId)->get();
        $sakipRadar = [
            'categories' => ['Perencanaan Kinerja (30%)', 'Pengukuran Kinerja (30%)', 'Pelaporan Kinerja (15%)', 'Evaluasi Internal (25%)'],
            'series' => [
                round($sakipEvals->avg('nilai_perencanaan') ?? 0, 1),
                round($sakipEvals->avg('nilai_pengukuran') ?? 0, 1),
                round($sakipEvals->avg('nilai_pelaporan') ?? 0, 1),
                round($sakipEvals->avg('nilai_evaluasi') ?? 0, 1),
            ],
        ];

        // 2. Renaksi Quarterly Breakdown
        $renaksiTw = [];
        foreach (['TW1', 'TW2', 'TW3', 'TW4'] as $tw) {
            $total = RencanaAksi::where('tahun_anggaran_id', $tahunId)->where('triwulan', $tw)->count();
            $done = RealisasiRenaksi::whereHas('rencanaAksi', fn ($q) => $q->where('tahun_anggaran_id', $tahunId)->where('triwulan', $tw))
                ->where('status', StatusRenaksi::Terverifikasi)
                ->count();
            $renaksiTw[] = [
                'triwulan' => $tw,
                'total' => $total,
                'selesai' => $done,
                'belum' => $total - $done,
            ];
        }

        // 3. LKE Status Distribution
        $lkeTargets = RbTargetPeriode::where('tahun_anggaran_id', $tahunId)->get();
        $lkeDistribution = [
            'labels' => ['Lengkap / Tercapai', 'Menunggu Verifikasi', 'Perlu Perbaikan', 'Belum Upload'],
            'series' => [
                $lkeTargets->whereIn('status_verifikasi', [StatusVerifikasiRb::Lengkap, StatusVerifikasiRb::Tercapai])->count(),
                $lkeTargets->where('status_verifikasi', StatusVerifikasiRb::BelumVerif)->count(),
                $lkeTargets->where('status_verifikasi', StatusVerifikasiRb::PerluPerbaikan)->count(),
                $lkeTargets->where('status_verifikasi', StatusVerifikasiRb::BelumUpload)->count(),
            ],
        ];

        // 4. SAKIP Ranking Top Satker
        $sakipRanking = EvaluasiSakip::with('satker')
            ->where('tahun_anggaran_id', $tahunId)
            ->orderBy('nilai_total', 'desc')
            ->take(8)
            ->get()
            ->map(fn ($e) => [
                'satker' => $e->satker->nama,
                'nilai' => (float)$e->nilai_total,
                'predikat' => $e->predikat->value,
            ]);

        return response()->json([
            'data' => [
                'sakip_radar' => $sakipRadar,
                'renaksi_quarterly' => $renaksiTw,
                'lke_distribution' => $lkeDistribution,
                'sakip_ranking' => $sakipRanking,
            ],
        ]);
    }
}