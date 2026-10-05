<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RbDokumenDaduk;
use App\Models\RbTargetPeriode;
use App\Services\FileUploadService;
use App\Enums\StatusVerifikasiRb;

class RbDokumenDadukController extends Controller
{
    public function __construct(
        protected FileUploadService $fileService
    ) {}

    /**
     * Upload Dokumen Data Dukung (Maks. 50MB, PDF only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'rb_target_periode_id' => 'required|exists:rb_target_periode,id',
            'file' => 'required|file|mimes:pdf|max:51200', // 50MB in KB
        ]);

        $target = RbTargetPeriode::findOrFail($request->rb_target_periode_id);
        $user = $request->user();

        if ($user->isOperatorSatker() && $user->satker_id !== $target->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($target->isLocked()) {
            abort(422, 'Workspace data dukung sudah terkunci karena status telah Lengkap/Tercapai.');
        }

        try {
            $fileData = $this->fileService->uploadDokumenDaduk(
                $request->file('file'),
                $target->id,
                $user->id
            );

            $dokumen = RbDokumenDaduk::create([
                'rb_target_periode_id' => $target->id,
                'nama_file' => $fileData['nama_file'],
                'path_file' => $fileData['path_file'],
                'ukuran_file' => $fileData['ukuran_file'],
                'mime_type' => $fileData['mime_type'],
                'uploaded_by' => $user->id,
            ]);

            return response()->json([
                'data' => $dokumen->load('uploadedBy'),
                'message' => 'Dokumen data dukung berhasil diunggah.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Hapus Dokumen Data Dukung
     */
    public function destroy(Request $request, RbDokumenDaduk $rbDokumenDaduk)
    {
        $target = $rbDokumenDaduk->targetPeriode;
        $user = $request->user();

        if ($user->isOperatorSatker() && $user->satker_id !== $target->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        if ($target->isLocked()) {
            abort(422, 'Workspace data dukung sudah terkunci dan berkas tidak dapat dihapus.');
        }

        $this->fileService->deleteFile($rbDokumenDaduk->path_file);
        $rbDokumenDaduk->delete();

        return response()->json(['message' => 'Dokumen data dukung berhasil dihapus.']);
    }
}
