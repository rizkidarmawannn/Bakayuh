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

// E-Performance & Master Read Routes (Portal Publik tanpa login)
Route::get('/satker', [SatuanKerjaController::class, 'index']);
Route::get('/satker/{satker}', [SatuanKerjaController::class, 'show']);

Route::get('/tahun-anggaran', [TahunAnggaranController::class, 'index']);
Route::get('/tahun-anggaran/{tahun_anggaran}', [TahunAnggaranController::class, 'show']);

Route::get('/indikator-kinerja', [IndikatorKinerjaController::class, 'index']);
Route::get('/indikator-kinerja/{indikator_kinerja}', [IndikatorKinerjaController::class, 'show']);

Route::get('/target-iku', [TargetIkuController::class, 'index']);
Route::get('/target-iku/matriks', [TargetIkuController::class, 'matriks']);
Route::get('/target-iku/{target_iku}', [TargetIkuController::class, 'show']);

Route::get('/realisasi-iku', [RealisasiIkuController::class, 'index']);
Route::get('/realisasi-iku/{realisasi_iku}', [RealisasiIkuController::class, 'show']);

Route::get('/rencana-aksi', [RencanaAksiController::class, 'index']);
Route::get('/rencana-aksi/{rencana_aksi}', [RencanaAksiController::class, 'show']);

Route::get('/evaluasi-sakip', [EvaluasiSakipController::class, 'index']);
Route::get('/evaluasi-sakip/perbandingan', [EvaluasiSakipController::class, 'perbandingan']);
Route::get('/evaluasi-sakip/tren', [EvaluasiSakipController::class, 'tren']);
Route::get('/evaluasi-sakip/{evaluasi_sakip}', [EvaluasiSakipController::class, 'show']);

Route::get('/dashboard/charts', [DashboardController::class, 'charts']);

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

    // Satuan Kerja (Mutations)
    Route::apiResource('satker', SatuanKerjaController::class)->except(['index', 'show']);

    // Tahun Anggaran (Mutations)
    Route::apiResource('tahun-anggaran', TahunAnggaranController::class)->except(['index', 'show']);
    Route::match(['put', 'patch'], '/tahun-anggaran/{tahun}/set-aktif', [TahunAnggaranController::class, 'setAktif']);

    // Indikator Kinerja (Master IKU Mutations)
    Route::apiResource('indikator-kinerja', IndikatorKinerjaController::class)->except(['index', 'show']);

    // Target IKU & Realisasi IKU (Mutations)
    Route::apiResource('target-iku', TargetIkuController::class)->except(['index', 'show']);
    Route::apiResource('realisasi-iku', RealisasiIkuController::class)->except(['index', 'show']);

    // Rencana Aksi (Mutations)
    Route::apiResource('rencana-aksi', RencanaAksiController::class)->except(['index', 'show']);

    // Realisasi Renaksi (Pelaporan & Verifikasi)
    Route::apiResource('realisasi-renaksi', RealisasiRenaksiController::class);
    Route::match(['put', 'patch'], '/realisasi-renaksi/{realisasi_renaksi}/submit', [RealisasiRenaksiController::class, 'submit']);
    Route::match(['put', 'patch'], '/realisasi-renaksi/{realisasi_renaksi}/verify', [RealisasiRenaksiController::class, 'verify']);

    // Bukti Dukung Renaksi (Upload maks. 10MB)
    Route::post('/bukti-dukung-renaksi', [BuktiDukungRenaksiController::class, 'store']);
    Route::delete('/bukti-dukung-renaksi/{bukti_dukung_renaksi}', [BuktiDukungRenaksiController::class, 'destroy']);

    // Evaluasi SAKIP (Mutations)
    Route::apiResource('evaluasi-sakip', EvaluasiSakipController::class)->except(['index', 'show']);

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