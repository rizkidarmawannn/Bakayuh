<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RealisasiRenaksi;
use App\Models\RencanaAksi;
use App\Enums\StatusRenaksi;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Enum;

class RealisasiRenaksiController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rencana_aksi_id' => 'required|exists:rencana_aksi,id',
            'deskripsi_realisasi' => 'required|string',
            'persentase_selesai' => 'required|integer|min:0|max:100',
        ]);

        $rencanaAksi = RencanaAksi::findOrFail($validated['rencana_aksi_id']);
        $user = $request->user();

        if ($user->isOperatorSatker() && $user->satker_id !== $rencanaAksi->satker_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk satuan kerja ini.');
        }

        $realisasi = RealisasiRenaksi::updateOrCreate(
            ['rencana_aksi_id' => $rencanaAksi->id],
            [
                'deskripsi_realisasi' => $validated['deskripsi_realisasi'],
                'persentase_selesai' => $validated['persentase_selesai'],
                'status' => StatusRenaksi::BelumLapor,
                'created_by' => $user->id,
            ]
        );

        return response()->json([
            'data' => $realisasi->load(['rencanaAksi', 'buktiDukung']),
            'message' => 'Laporan realisasi berhasil disimpan.',
        ], 201);
    }

    public function show(RealisasiRenaksi $realisasiRenaksi)
    {
        return response()->json([
            'data' => $realisasiRenaksi->load(['rencanaAksi.satker', 'rencanaAksi.indikator', 'buktiDukung', 'creator', 'verifikator'])
        ]);
    }

    public function update(Request $request, RealisasiRenaksi $realisasiRenaksi)
    {
        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== $realisasiRenaksi->rencanaAksi->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$realisasiRenaksi->isEditable()) {
            abort(422, 'Laporan tidak dapat diedit karena sedang menunggu verifikasi atau sudah terverifikasi.');
        }

        $validated = $request->validate([
            'deskripsi_realisasi' => 'required|string',
            'persentase_selesai' => 'required|integer|min:0|max:100',
        ]);

        $realisasiRenaksi->update($validated);

        return response()->json([
            'data' => $realisasiRenaksi->load('buktiDukung'),
            'message' => 'Laporan realisasi berhasil diperbarui.',
        ]);
    }

    /**
     * Partial update: Operator mengajukan laporan untuk diverifikasi (PATCH).
     */
    public function submit(Request $request, RealisasiRenaksi $realisasiRenaksi)
    {
        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== $realisasiRenaksi->rencanaAksi->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($realisasiRenaksi->buktiDukung()->count() === 0) {
            abort(422, 'Wajib mengunggah minimal 1 dokumen bukti dukung sebelum mengajukan verifikasi.');
        }

        $realisasiRenaksi->update([
            'status' => StatusRenaksi::MenungguVerifikasi,
            'submitted_at' => Carbon::now(),
        ]);

        return response()->json([
            'data' => $realisasiRenaksi->load('buktiDukung'),
            'message' => 'Laporan berhasil diajukan untuk verifikasi Kanwil.',
        ]);
    }

    /**
     * Partial update: Admin Kanwil memverifikasi atau mengembalikan revisi (PATCH).
     */
    public function verify(Request $request, RealisasiRenaksi $realisasiRenaksi)
    {
        $user = $request->user();
        if (!$user->canVerify()) {
            abort(403, 'Hanya Admin Kanwil / Verifikator yang berhak melakukan verifikasi.');
        }

        $validated = $request->validate([
            'status' => ['required', new Enum(StatusRenaksi::class)],
            'catatan_verifikasi' => 'nullable|string',
        ]);

        if (!in_array($validated['status'], [StatusRenaksi::Terverifikasi->value, StatusRenaksi::PerluPerbaikan->value])) {
            abort(422, 'Status verifikasi harus berupa "terverifikasi" atau "perlu_perbaikan".');
        }

        $realisasiRenaksi->update([
            'status' => $validated['status'],
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
            'verified_by' => $user->id,
            'verified_at' => Carbon::now(),
        ]);

        return response()->json([
            'data' => $realisasiRenaksi->load(['verifikator', 'buktiDukung']),
            'message' => 'Status verifikasi berhasil diperbarui.',
        ]);
    }
}