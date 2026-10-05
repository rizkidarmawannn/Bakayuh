<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RbTargetPeriode;
use App\Models\RbSubIndikator;
use App\Models\RbCatatanVerifikasi;
use App\Models\TahunAnggaran;
use App\Enums\PeriodeRb;
use App\Enums\StatusVerifikasiRb;
use App\Enums\KategoriRb;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Enum;

class RbTargetPeriodeController extends Controller
{
    /**
     * List target periode per Satker & Tahun
     */
    public function index(Request $request)
    {
        $request->validate([
            'satker_id' => 'required|exists:satuan_kerja,id',
            'tahun_anggaran_id' => 'nullable|exists:tahun_anggaran,id',
            'periode' => 'nullable|string',
            'kategori' => 'nullable|string',
            'sub_indikator_id' => 'nullable|exists:rb_sub_indikator,id',
        ]);

        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);
        $kategori = $request->input('kategori', KategoriRb::LkeWbkWbbm->value);
        $satkerId = $request->satker_id;

        $query = RbTargetPeriode::with([
            'subIndikator.indikator.area',
            'dokumen',
            'catatan.sender',
            'verifikator',
        ])
            ->where('satker_id', $satkerId)
            ->where('tahun_anggaran_id', $tahunId);

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->filled('sub_indikator_id')) {
            $query->where('rb_sub_indikator_id', $request->sub_indikator_id);
        }

        if ($kategori) {
            $query->whereHas('subIndikator.indikator.area', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        $items = $query->get();

        // Append computed countdown
        $items->each(function ($item) {
            $item->append('countdown');
        });

        return response()->json(['data' => $items]);
    }

    /**
     * Get or initialize a target periode for a specific sub-indicator
     */
    public function getOrInit(Request $request)
    {
        $validated = $request->validate([
            'rb_sub_indikator_id' => 'required|exists:rb_sub_indikator,id',
            'tahun_anggaran_id' => 'required|exists:tahun_anggaran,id',
            'satker_id' => 'required|exists:satuan_kerja,id',
            'periode' => ['required', new Enum(PeriodeRb::class)],
        ]);

        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== (int)$validated['satker_id']) {
            abort(403, 'Akses ditolak.');
        }

        $target = RbTargetPeriode::firstOrCreate(
            [
                'rb_sub_indikator_id' => $validated['rb_sub_indikator_id'],
                'tahun_anggaran_id' => $validated['tahun_anggaran_id'],
                'satker_id' => $validated['satker_id'],
                'periode' => $validated['periode'],
            ],
            [
                'status_verifikasi' => StatusVerifikasiRb::BelumUpload,
            ]
        );

        $target->load([
            'subIndikator.indikator.area',
            'dokumen.uploadedBy',
            'catatan.sender',
            'verifikator',
        ]);
        $target->append('countdown');

        return response()->json(['data' => $target]);
    }

    /**
     * Detail Target Periode
     */
    public function show(RbTargetPeriode $rbTargetPeriode)
    {
        $rbTargetPeriode->load([
            'subIndikator.indikator.area',
            'satker',
            'tahunAnggaran',
            'dokumen.uploadedBy',
            'catatan.sender',
            'verifikator',
        ]);
        $rbTargetPeriode->append('countdown');

        return response()->json(['data' => $rbTargetPeriode]);
    }

    /**
     * Update Penjelasan ZI atau Batas Waktu
     */
    public function update(Request $request, RbTargetPeriode $rbTargetPeriode)
    {
        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== $rbTargetPeriode->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($user->isOperatorSatker() && $rbTargetPeriode->isLocked()) {
            abort(422, 'Data dukung sudah terkunci dan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'penjelasan_zi' => 'nullable|string',
            'batas_waktu_upload' => 'nullable|date',
        ]);

        $updateData = [];
        if (array_key_exists('penjelasan_zi', $validated)) {
            $updateData['penjelasan_zi'] = $validated['penjelasan_zi'];
        }

        // Only Kanwil or SuperAdmin can set or modify deadline
        if ($user->canVerify() && array_key_exists('batas_waktu_upload', $validated)) {
            $updateData['batas_waktu_upload'] = $validated['batas_waktu_upload'];
        }

        $rbTargetPeriode->update($updateData);

        return response()->json([
            'data' => $rbTargetPeriode->load(['dokumen', 'catatan.sender', 'verifikator']),
            'message' => 'Data target periode berhasil diperbarui.',
        ]);
    }

    /**
     * Operator Satker: Submit Daduk for Kanwil Verification (PATCH)
     */
    public function submit(Request $request, RbTargetPeriode $rbTargetPeriode)
    {
        $user = $request->user();
        if ($user->isOperatorSatker() && $user->satker_id !== $rbTargetPeriode->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($rbTargetPeriode->isLocked()) {
            abort(422, 'Data dukung sudah terkunci (Lengkap/Tercapai).');
        }

        if ($rbTargetPeriode->dokumen()->count() === 0) {
            abort(422, 'Wajib mengunggah minimal 1 dokumen data dukung sebelum mengajukan verifikasi.');
        }

        $rbTargetPeriode->update([
            'status_verifikasi' => StatusVerifikasiRb::BelumVerif,
        ]);

        return response()->json([
            'data' => $rbTargetPeriode->load(['dokumen', 'catatan.sender']),
            'message' => 'Data dukung berhasil diajukan untuk verifikasi Tim Kanwil.',
        ]);
    }

    /**
     * Admin Kanwil / Super Admin: Verify Daduk (PATCH)
     */
    public function verify(Request $request, RbTargetPeriode $rbTargetPeriode)
    {
        $user = $request->user();
        if (!$user->canVerify()) {
            abort(403, 'Hanya Admin Kanwil / Super Admin yang berhak memverifikasi data dukung.');
        }

        $validated = $request->validate([
            'status_verifikasi' => ['required', new Enum(StatusVerifikasiRb::class)],
            'catatan' => 'nullable|string',
        ]);

        $status = $validated['status_verifikasi'];
        if (!in_array($status, [StatusVerifikasiRb::Lengkap->value, StatusVerifikasiRb::PerluPerbaikan->value, StatusVerifikasiRb::Tercapai->value])) {
            abort(422, 'Status verifikasi harus berupa "lengkap", "perlu_perbaikan", atau "tercapai".');
        }

        $rbTargetPeriode->update([
            'status_verifikasi' => $status,
            'verified_by' => $user->id,
            'verified_at' => Carbon::now(),
        ]);

        // Add clarification thread message if note is supplied
        if (!empty($validated['catatan'])) {
            RbCatatanVerifikasi::create([
                'rb_target_periode_id' => $rbTargetPeriode->id,
                'sender_id' => $user->id,
                'pesan' => $validated['catatan'],
            ]);
        }

        return response()->json([
            'data' => $rbTargetPeriode->load(['dokumen', 'catatan.sender', 'verifikator']),
            'message' => 'Status verifikasi data dukung berhasil diperbarui.',
        ]);
    }
}
