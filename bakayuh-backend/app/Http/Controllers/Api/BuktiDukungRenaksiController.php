<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuktiDukungRenaksi;
use App\Models\RealisasiRenaksi;
use App\Services\FileUploadService;

class BuktiDukungRenaksiController extends Controller
{
    public function __construct(
        protected FileUploadService $fileService
    ) {}

    public function store(Request $request)
    {
        $request->validate([
            'realisasi_renaksi_id' => 'required|exists:realisasi_renaksi,id',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB in KB
        ]);

        $realisasi = RealisasiRenaksi::findOrFail($request->realisasi_renaksi_id);
        $user = $request->user();

        if ($user->isOperatorSatker() && $user->satker_id !== $realisasi->rencanaAksi->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$realisasi->isEditable()) {
            abort(422, 'Tidak dapat menambah bukti dukung karena status laporan sedang diverifikasi atau sudah selesai.');
        }

        try {
            $fileData = $this->fileService->uploadBuktiRenaksi(
                $request->file('file'),
                $realisasi->id,
                $user->id
            );

            $bukti = BuktiDukungRenaksi::create([
                'realisasi_renaksi_id' => $realisasi->id,
                'nama_file' => $fileData['nama_file'],
                'path_file' => $fileData['path_file'],
                'ukuran_file' => $fileData['ukuran_file'],
                'mime_type' => $fileData['mime_type'],
                'uploaded_by' => $user->id,
            ]);

            return response()->json([
                'data' => $bukti,
                'message' => 'Bukti dukung berhasil diunggah.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, BuktiDukungRenaksi $buktiDukungRenaksi)
    {
        $realisasi = $buktiDukungRenaksi->realisasiRenaksi;
        $user = $request->user();

        if ($user->isOperatorSatker() && $user->satker_id !== $realisasi->rencanaAksi->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$realisasi->isEditable()) {
            abort(422, 'Tidak dapat menghapus berkas yang telah diverifikasi.');
        }

        $this->fileService->deleteFile($buktiDukungRenaksi->path_file);
        $buktiDukungRenaksi->delete();

        return response()->json(['message' => 'Bukti dukung berhasil dihapus.']);
    }
}