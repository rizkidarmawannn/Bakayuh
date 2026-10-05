<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RbArea;
use App\Models\RbTargetPeriode;
use App\Models\TahunAnggaran;
use App\Enums\KategoriRb;
use App\Enums\KomponenRb;
use App\Enums\StatusVerifikasiRb;
use Illuminate\Validation\Rules\Enum;

class RbAreaController extends Controller
{
    /**
     * Get hierarchy of RB Areas with Indikators and Sub-Indikators
     */
    public function index(Request $request)
    {
        $kategori = $request->input('kategori', KategoriRb::LkeWbkWbbm->value);

        $query = RbArea::with([
            'indikator' => function ($q) {
                $q->orderBy('urutan')->orderBy('kode');
            },
            'indikator.subIndikator' => function ($q) {
                $q->orderBy('nomor_poin');
            },
        ])->where('kategori', $kategori);

        if ($request->filled('komponen')) {
            $query->where('komponen', $request->komponen);
        }

        $areas = $query->orderBy('urutan')->orderBy('kode')->get();

        return response()->json(['data' => $areas]);
    }

    /**
     * Calculate Progress for LKE ZI / RKT RB
     */
    public function progress(Request $request)
    {
        $request->validate([
            'satker_id' => 'required|exists:satuan_kerja,id',
            'tahun_anggaran_id' => 'nullable|exists:tahun_anggaran,id',
            'periode' => 'nullable|string',
            'kategori' => 'nullable|string',
        ]);

        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);
        $kategori = $request->input('kategori', KategoriRb::LkeWbkWbbm->value);

        $query = RbTargetPeriode::where('satker_id', $request->satker_id)
            ->where('tahun_anggaran_id', $tahunId)
            ->whereHas('subIndikator.indikator.area', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        $targets = $query->get();
        $totalTargets = $targets->count();
        $lengkapCount = $targets->whereIn('status_verifikasi', [StatusVerifikasiRb::Lengkap, StatusVerifikasiRb::Tercapai])->count();
        $belumVerifCount = $targets->where('status_verifikasi', StatusVerifikasiRb::BelumVerif)->count();
        $perluPerbaikanCount = $targets->where('status_verifikasi', StatusVerifikasiRb::PerluPerbaikan)->count();
        $belumUploadCount = $targets->where('status_verifikasi', StatusVerifikasiRb::BelumUpload)->count();

        $percentage = $totalTargets > 0 ? round(($lengkapCount / $totalTargets) * 100, 2) : 0;

        return response()->json([
            'data' => [
                'total_targets' => $totalTargets,
                'lengkap_count' => $lengkapCount,
                'belum_verif_count' => $belumVerifCount,
                'perlu_perbaikan_count' => $perluPerbaikanCount,
                'belum_upload_count' => $belumUploadCount,
                'percentage' => $percentage,
            ],
        ]);
    }
}
