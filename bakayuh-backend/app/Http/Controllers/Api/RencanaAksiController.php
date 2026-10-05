<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RencanaAksi;
use App\Models\TahunAnggaran;
use App\Enums\Triwulan;
use Illuminate\Validation\Rules\Enum;

class RencanaAksiController extends Controller
{
    public function index(Request $request)
    {
        $query = RencanaAksi::with(['satker', 'indikator', 'realisasi.buktiDukung', 'tahunAnggaran']);

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

        if ($request->filled('triwulan')) {
            $query->where('triwulan', $request->triwulan);
        }

        $items = $query->orderBy('triwulan')->orderBy('nama_aksi')->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
            'satker_id' => 'required|exists:satuan_kerja,id',
            'indikator_id' => 'required|exists:indikator_kinerja,id',
            'nama_aksi' => 'required|string|max:255',
            'triwulan' => ['required', new Enum(Triwulan::class)],
            'target_output' => 'nullable|string',
        ]);

        $renaksi = RencanaAksi::create($validated);

        return response()->json([
            'data' => $renaksi->load(['satker', 'indikator', 'realisasi']),
            'message' => 'Rencana aksi berhasil dibuat.',
        ], 201);
    }

    public function show(RencanaAksi $rencanaAksi)
    {
        return response()->json([
            'data' => $rencanaAksi->load(['satker', 'indikator', 'realisasi.buktiDukung', 'tahunAnggaran'])
        ]);
    }

    public function update(Request $request, RencanaAksi $rencanaAksi)
    {
        $validated = $request->validate([
            'nama_aksi' => 'required|string|max:255',
            'triwulan' => ['required', new Enum(Triwulan::class)],
            'target_output' => 'nullable|string',
        ]);

        $rencanaAksi->update($validated);

        return response()->json([
            'data' => $rencanaAksi->load(['satker', 'indikator', 'realisasi']),
            'message' => 'Rencana aksi berhasil diperbarui.',
        ]);
    }

    public function destroy(RencanaAksi $rencanaAksi)
    {
        $rencanaAksi->delete();

        return response()->json(['message' => 'Rencana aksi berhasil dihapus.']);
    }
}