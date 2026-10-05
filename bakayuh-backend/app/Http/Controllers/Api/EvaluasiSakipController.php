<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EvaluasiSakip;
use App\Models\SatuanKerja;
use App\Models\TahunAnggaran;
use App\Enums\PredikatSakip;

class EvaluasiSakipController extends Controller
{
    public function index(Request $request)
    {
        $query = EvaluasiSakip::with(['satker', 'tahunAnggaran', 'creator']);

        if ($request->filled('tahun_anggaran_id')) {
            $query->where('tahun_anggaran_id', $request->tahun_anggaran_id);
        } else {
            $aktif = TahunAnggaran::getAktif();
            if ($aktif) {
                $query->where('tahun_anggaran_id', $aktif->id);
            }
        }

        if ($request->filled('satker_id')) {
            $query->where('satker_id', $request->satker_id);
        }

        $items = $query->orderBy('nilai_total', 'desc')->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->canVerify()) {
            abort(403, 'Hanya Admin Kanwil yang berhak mengevaluasi nilai SAKIP.');
        }

        $validated = $request->validate([
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
            'satker_id' => 'required|exists:satuan_kerja,id',
            'nilai_perencanaan' => 'required|numeric|min:0|max:100',
            'nilai_pengukuran' => 'required|numeric|min:0|max:100',
            'nilai_pelaporan' => 'required|numeric|min:0|max:100',
            'nilai_evaluasi' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        // Kalkulasi bobot otomatis
        $total = round(
            ((float)$validated['nilai_perencanaan'] * 0.30) +
            ((float)$validated['nilai_pengukuran']  * 0.30) +
            ((float)$validated['nilai_pelaporan']   * 0.15) +
            ((float)$validated['nilai_evaluasi']    * 0.25), 2
        );

        $predikat = PredikatSakip::fromNilai($total);

        $sakip = EvaluasiSakip::updateOrCreate(
            [
                'tahun_anggaran_id' => $validated['tahun_anggaran_id'],
                'satker_id' => $validated['satker_id'],
            ],
            [
                'nilai_perencanaan' => $validated['nilai_perencanaan'],
                'nilai_pengukuran' => $validated['nilai_pengukuran'],
                'nilai_pelaporan' => $validated['nilai_pelaporan'],
                'nilai_evaluasi' => $validated['nilai_evaluasi'],
                'nilai_total' => $total,
                'predikat' => $predikat,
                'catatan' => $validated['catatan'] ?? null,
                'created_by' => $user->id,
            ]
        );

        return response()->json([
            'data' => $sakip->load(['satker', 'tahunAnggaran']),
            'message' => 'Evaluasi SAKIP berhasil disimpan.',
        ], 201);
    }

    public function show(EvaluasiSakip $evaluasiSakip)
    {
        return response()->json([
            'data' => $evaluasiSakip->load(['satker', 'tahunAnggaran', 'creator'])
        ]);
    }

    public function update(Request $request, EvaluasiSakip $evaluasiSakip)
    {
        $user = $request->user();
        if (!$user->canVerify()) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'nilai_perencanaan' => 'required|numeric|min:0|max:100',
            'nilai_pengukuran' => 'required|numeric|min:0|max:100',
            'nilai_pelaporan' => 'required|numeric|min:0|max:100',
            'nilai_evaluasi' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        $total = round(
            ((float)$validated['nilai_perencanaan'] * 0.30) +
            ((float)$validated['nilai_pengukuran']  * 0.30) +
            ((float)$validated['nilai_pelaporan']   * 0.15) +
            ((float)$validated['nilai_evaluasi']    * 0.25), 2
        );

        $predikat = PredikatSakip::fromNilai($total);

        $evaluasiSakip->update([
            'nilai_perencanaan' => $validated['nilai_perencanaan'],
            'nilai_pengukuran' => $validated['nilai_pengukuran'],
            'nilai_pelaporan' => $validated['nilai_pelaporan'],
            'nilai_evaluasi' => $validated['nilai_evaluasi'],
            'nilai_total' => $total,
            'predikat' => $predikat,
            'catatan' => $validated['catatan'] ?? $evaluasiSakip->catatan,
        ]);

        return response()->json([
            'data' => $evaluasiSakip->load(['satker', 'tahunAnggaran']),
            'message' => 'Evaluasi SAKIP berhasil diperbarui.',
        ]);
    }

    public function destroy(EvaluasiSakip $evaluasiSakip)
    {
        $evaluasiSakip->delete();

        return response()->json(['message' => 'Data evaluasi SAKIP berhasil dihapus.']);
    }

    /**
     * Tabel komparasi nilai SAKIP seluruh satker pada tahun terpilih
     */
    public function perbandingan(Request $request)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);

        $satkers = SatuanKerja::where('is_active', true)->orderBy('tipe')->orderBy('nama')->get();
        $evaluasis = EvaluasiSakip::where('tahun_anggaran_id', $tahunId)->get()->keyBy('satker_id');

        $data = $satkers->map(function ($satker) use ($evaluasis) {
            $eval = $evaluasis->get($satker->id);

            return [
                'satker_id' => $satker->id,
                'kode' => $satker->kode,
                'nama' => $satker->nama,
                'tipe' => $satker->tipe->value,
                'evaluasi_id' => $eval?->id,
                'nilai_perencanaan' => $eval ? (float)$eval->nilai_perencanaan : null,
                'nilai_pengukuran' => $eval ? (float)$eval->nilai_pengukuran : null,
                'nilai_pelaporan' => $eval ? (float)$eval->nilai_pelaporan : null,
                'nilai_evaluasi' => $eval ? (float)$eval->nilai_evaluasi : null,
                'nilai_total' => $eval ? (float)$eval->nilai_total : null,
                'predikat' => $eval?->predikat?->value ?? null,
                'predikat_label' => $eval?->predikat?->label() ?? null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Data Tren perkembangan nilai SAKIP antar tahun
     */
    public function tren(Request $request)
    {
        $satkerId = $request->input('satker_id');

        $query = EvaluasiSakip::with(['satker', 'tahunAnggaran'])->orderBy('tahun_anggaran_id');

        if ($satkerId) {
            $query->where('satker_id', $satkerId);
        }

        $items = $query->get()->map(function ($item) {
            return [
                'tahun' => $item->tahunAnggaran->tahun,
                'satker' => $item->satker->nama,
                'nilai_total' => (float)$item->nilai_total,
                'predikat' => $item->predikat->value,
            ];
        });

        return response()->json(['data' => $items]);
    }
}