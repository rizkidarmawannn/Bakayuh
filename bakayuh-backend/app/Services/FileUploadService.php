<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload bukti dukung Renaksi (maksimal 10MB = 10240 KB).
     * Format: PDF, JPG, JPEG, PNG.
     */
    public function uploadBuktiRenaksi(UploadedFile $file, int $realisasiRenaksiId, int $userId): array
    {
        $maxBytes = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxBytes) {
            throw new \InvalidArgumentException('Ukuran berkas bukti dukung melebihi batas maksimal 10MB.');
        }

        $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Format berkas harus berupa PDF, JPG, atau PNG.');
        }

        $originalName = $file->getClientOriginalName();
        $safeName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("bukti_renaksi/{$realisasiRenaksiId}", $safeName, 'public');

        return [
            'nama_file' => $originalName,
            'path_file' => $path,
            'ukuran_file' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $userId,
        ];
    }

    /**
     * Upload dokumen data dukung RB / LKE ZI (maksimal 50MB = 51200 KB).
     * Format: PDF only.
     */
    public function uploadDokumenDaduk(UploadedFile $file, int $rbTargetPeriodeId, int $userId): array
    {
        $maxBytes = 50 * 1024 * 1024; // 50MB
        if ($file->getSize() > $maxBytes) {
            throw new \InvalidArgumentException('Ukuran berkas data dukung melebihi batas maksimal 50MB.');
        }

        $allowedMimes = ['application/pdf'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('Format data dukung ZI wajib berupa berkas PDF.');
        }

        $originalName = $file->getClientOriginalName();
        $safeName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("dokumen_daduk/{$rbTargetPeriodeId}", $safeName, 'public');

        return [
            'nama_file' => $originalName,
            'path_file' => $path,
            'ukuran_file' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_by' => $userId,
        ];
    }

    /**
     * Hapus berkas dari disk publik.
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
}