<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SatuanKerjaController;
use App\Http\Controllers\Api\TahunAnggaranController;
use App\Http\Controllers\Api\IndikatorKinerjaController;
use App\Http\Controllers\Api\TargetIkuController;
use App\Http\Controllers\Api\RealisasiIkuController;
use App\Http\Controllers\Api\RencanaAksiController;
use App\Http\Controllers\Api\RealisasiRenaksiController;
use App\Http\Controllers\Api\BuktiDukungRenaksiController;
use App\Http\Controllers\Api\EvaluasiSakipController;
use App\Http\Controllers\Api\RbAreaController;
use App\Http\Controllers\Api\RbTargetPeriodeController;
use App\Http\Controllers\Api\RbDokumenDadukController;
use App\Http\Controllers\Api\RbCatatanVerifikasiController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PublikController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::get('/publik/summary', [PublikController::class, 'summary']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth profile
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::match(['put', 'patch'], '/password', [AuthController::class, 'updatePassword']);
    });

    // Dashboard
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

    // Satuan Kerja
    Route::apiResource('satker', SatuanKerjaController::class);

    // Tahun Anggaran
    Route::apiResource('tahun-anggaran', TahunAnggaranController::class);
    Route::match(['put', 'patch'], '/tahun-anggaran/{tahun}/set-aktif', [TahunAnggaranController::class, 'setAktif']);

    // Indikator Kinerja (Master IKU)
    Route::apiResource('indikator-kinerja', IndikatorKinerjaController::class);

    // Target IKU & Realisasi IKU
    Route::get('/target-iku/matriks', [TargetIkuController::class, 'matriks']);
    Route::apiResource('target-iku', TargetIkuController::class);
    Route::apiResource('realisasi-iku', RealisasiIkuController::class);

    // Rencana Aksi (Renaksi TW I - IV)
    Route::apiResource('rencana-aksi', RencanaAksiController::class);

    // Realisasi Renaksi (Pelaporan & Verifikasi)
    Route::apiResource('realisasi-renaksi', RealisasiRenaksiController::class);
    Route::match(['put', 'patch'], '/realisasi-renaksi/{realisasi_renaksi}/submit', [RealisasiRenaksiController::class, 'submit']);
    Route::match(['put', 'patch'], '/realisasi-renaksi/{realisasi_renaksi}/verify', [RealisasiRenaksiController::class, 'verify']);

    // Bukti Dukung Renaksi (Upload maks. 10MB)
    Route::post('/bukti-dukung-renaksi', [BuktiDukungRenaksiController::class, 'store']);
    Route::delete('/bukti-dukung-renaksi/{bukti_dukung_renaksi}', [BuktiDukungRenaksiController::class, 'destroy']);

    // Evaluasi SAKIP (4 Komponen)
    Route::get('/evaluasi-sakip/perbandingan', [EvaluasiSakipController::class, 'perbandingan']);
    Route::get('/evaluasi-sakip/tren', [EvaluasiSakipController::class, 'tren']);
    Route::apiResource('evaluasi-sakip', EvaluasiSakipController::class);

    // E-RB & LKE ZI (Area, Indikator, Sub-Indikator, Target Periode, Daduk Workspace, Chat Klarifikasi)
    Route::get('/rb-area', [RbAreaController::class, 'index']);
    Route::get('/rb-area/progress', [RbAreaController::class, 'progress']);

    Route::get('/rb-target-periode', [RbTargetPeriodeController::class, 'index']);
    Route::post('/rb-target-periode/get-or-init', [RbTargetPeriodeController::class, 'getOrInit']);
    Route::get('/rb-target-periode/{rbTargetPeriode}', [RbTargetPeriodeController::class, 'show']);
    Route::put('/rb-target-periode/{rbTargetPeriode}', [RbTargetPeriodeController::class, 'update']);
    Route::match(['put', 'patch'], '/rb-target-periode/{rbTargetPeriode}/submit', [RbTargetPeriodeController::class, 'submit']);
    Route::match(['put', 'patch'], '/rb-target-periode/{rbTargetPeriode}/verify', [RbTargetPeriodeController::class, 'verify']);

    // Dokumen Data Dukung (Upload PDF maks. 50MB)
    Route::post('/rb-dokumen-daduk', [RbDokumenDadukController::class, 'store']);
    Route::delete('/rb-dokumen-daduk/{rbDokumenDaduk}', [RbDokumenDadukController::class, 'destroy']);

    // Catatan Verifikasi / Klarifikasi Dua Arah
    Route::get('/rb-catatan-verifikasi', [RbCatatanVerifikasiController::class, 'index']);
    Route::post('/rb-catatan-verifikasi', [RbCatatanVerifikasiController::class, 'store']);

    // User Management (Super Admin)
    Route::apiResource('users', UserController::class);
    Route::match(['put', 'patch'], '/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
    Route::match(['put', 'patch'], '/users/{user}/reset-password', [UserController::class, 'resetPassword']);

    // Export Excel & PDF
    Route::get('/export/iku/excel', [ExportController::class, 'exportIkuExcel']);
    Route::get('/export/sakip/excel', [ExportController::class, 'exportSakipExcel']);
    Route::get('/export/sakip/pdf/{satkerId}', [ExportController::class, 'exportSakipPdf']);
    Route::get('/export/renaksi/pdf/{id}', [ExportController::class, 'exportRenaksiPdf']);
});