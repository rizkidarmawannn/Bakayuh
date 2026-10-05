<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TargetIku;
use App\Models\SatuanKerja;
use App\Models\IndikatorKinerja;
use App\Models\TahunAnggaran;

class TargetIkuController extends Controller
{
    public function index(Request $request)
    {
        $query = TargetIku::with(['satker', 'indikator', 'realisasi', 'tahunAnggaran']);

        if ($request->filled('tahun_anggaran_id')) {
            $query->where('tahun_anggaran_id', $request->tahun_anggaran_id);
        } else {
            $tahunAktif = TahunAnggaran::getAktif();
            if ($tahunAktif) {
                $query->where('tahun_anggaran_id', $tahunAktif->id);
            }
        }

        if ($request->filled('satker_id')) {
            $query->where('satker_id', $request->satker_id);
        }

        $targets = $query->get();

        return response()->json(['data' => $targets]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
            'satker_id' => 'required|exists:satuan_kerja,id',
            'indikator_id' => 'required|exists:indikator_kinerja,id',
            'nilai_target' => 'required|numeric|min:0',
        ]);

        $target = TargetIku::updateOrCreate(
            [
                'tahun_anggaran_id' => $validated['tahun_anggaran_id'],
                'satker_id' => $validated['satker_id'],
                'indikator_id' => $validated['indikator_id'],
            ],
            ['nilai_target' => $validated['nilai_target']]
        );

        return response()->json([
            'data' => $target->load(['satker', 'indikator', 'realisasi']),
            'message' => 'Target IKU berhasil disimpan.',
        ], 201);
    }

    public function show(TargetIku $targetIku)
    {
        return response()->json(['data' => $targetIku->load(['satker', 'indikator', 'realisasi'])]);
    }

    public function update(Request $request, TargetIku $targetIku)
    {
        $validated = $request->validate([
            'nilai_target' => 'required|numeric|min:0',
        ]);

        $targetIku->update($validated);

        return response()->json([
            'data' => $targetIku->load(['satker', 'indikator', 'realisasi']),
            'message' => 'Target IKU berhasil diperbarui.',
        ]);
    }

    public function destroy(TargetIku $targetIku)
    {
        $targetIku->delete();

        return response()->json(['message' => 'Target IKU berhasil dihapus.']);
    }

    /**
     * Matriks Capaian IKU seluruh Satuan Kerja
     */
    public function matriks(Request $request)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);

        $satkers = SatuanKerja::where('is_active', true)->orderBy('tipe')->orderBy('nama')->get();
        $indikators = IndikatorKinerja::orderBy('kode')->get();

        $targets = TargetIku::with('realisasi')
            ->where('tahun_anggaran_id', $tahunId)
            ->get()
            ->keyBy(fn ($t) => "{$t->satker_id}_{$t->indikator_id}");

        $matriks = $satkers->map(function ($satker) use ($indikators, $targets) {
            $rowIndikators = $indikators->map(function ($ind) use ($satker, $targets) {
                $target = $targets->get("{$satker->id}_{$ind->id}");
                $realisasi = $target?->realisasi;

                $targetVal = $target ? (float)$target->nilai_target : null;
                $realisasiVal = $realisasi ? (float)$realisasi->nilai_realisasi : null;
                $persenVal = $realisasi ? (float)$realisasi->persentase_capaian : null;

                $color = 'gray';
                if ($persenVal !== null) {
                    $color = match (true) {
                        $persenVal >= 100 => 'green',
                        $persenVal >= 80  => 'yellow',
                        default           => 'red',
                    };
                }

                return [
                    'indikator_id' => $ind->id,
                    'kode' => $ind->kode,
                    'nama' => $ind->nama,
                    'satuan' => $ind->satuan,
                    'target_iku_id' => $target?->id,
                    'target' => $targetVal,
                    'realisasi' => $realisasiVal,
                    'persentase' => $persenVal,
                    'status_color' => $color,
                ];
            });

            return [
                'satker' => [
                    'id' => $satker->id,
                    'kode' => $satker->kode,
                    'nama' => $satker->nama,
                    'tipe' => $satker->tipe->value,
                ],
                'indikators' => $rowIndikators,
            ];
        });

        return response()->json(['data' => $matriks]);
    }
}