<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RealisasiIku;
use App\Models\TargetIku;
use App\Services\PersentaseCapaianService;

class RealisasiIkuController extends Controller
{
    public function __construct(
        protected PersentaseCapaianService $persentaseService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_iku_id' => 'required|exists:target_iku,id',
            'nilai_realisasi' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $target = TargetIku::with('indikator')->findOrFail($validated['target_iku_id']);

        // Check permission: operator satker can only input for own satker
        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== $target->satker_id) {
            abort(403, 'Anda hanya dapat menginput realisasi untuk satuan kerja Anda.');
        }

        $persen = $this->persentaseService->hitung(
            (float)$target->nilai_target,
            (float)$validated['nilai_realisasi'],
            $target->indikator->polaritas
        );

        $realisasi = RealisasiIku::updateOrCreate(
            ['target_iku_id' => $target->id],
            [
                'nilai_realisasi' => $validated['nilai_realisasi'],
                'persentase_capaian' => $persen,
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => $user->id,
            ]
        );

        return response()->json([
            'data' => $realisasi->load('targetIku.indikator'),
            'message' => 'Realisasi IKU berhasil disimpan.',
        ], 201);
    }

    public function show(RealisasiIku $realisasiIku)
    {
        return response()->json(['data' => $realisasiIku->load('targetIku.indikator', 'creator')]);
    }

    public function update(Request $request, RealisasiIku $realisasiIku)
    {
        $validated = $request->validate([
            'nilai_realisasi' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $target = $realisasiIku->targetIku->load('indikator');

        // Check permission
        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== $target->satker_id) {
            abort(403, 'Anda hanya dapat mengubah realisasi untuk satuan kerja Anda.');
        }

        $persen = $this->persentaseService->hitung(
            (float)$target->nilai_target,
            (float)$validated['nilai_realisasi'],
            $target->indikator->polaritas
        );

        $realisasiIku->update([
            'nilai_realisasi' => $validated['nilai_realisasi'],
            'persentase_capaian' => $persen,
            'keterangan' => $validated['keterangan'] ?? $realisasiIku->keterangan,
        ]);

        return response()->json([
            'data' => $realisasiIku->load('targetIku.indikator'),
            'message' => 'Realisasi IKU berhasil diperbarui.',
        ]);
    }

    public function destroy(RealisasiIku $realisasiIku)
    {
        $realisasiIku->delete();

        return response()->json(['message' => 'Realisasi IKU berhasil dihapus.']);
    }
}