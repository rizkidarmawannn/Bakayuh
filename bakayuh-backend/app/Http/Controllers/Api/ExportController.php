<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahunAnggaran;
use App\Models\EvaluasiSakip;
use App\Models\RencanaAksi;
use App\Exports\IkuMatriksExport;
use App\Exports\SakipPerbandinganExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export IKU Matriks to Excel
     */
    public function exportIkuExcel(Request $request)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);
        $tahun = TahunAnggaran::find($tahunId);
        $tahunNumber = $tahun?->tahun ?? date('Y');

        return Excel::download(
            new IkuMatriksExport($tahunId),
            "Matriks_IKU_Kemenkumham_Kalsel_{$tahunNumber}.xlsx"
        );
    }

    /**
     * Export SAKIP Perbandingan to Excel
     */
    public function exportSakipExcel(Request $request)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);
        $tahun = TahunAnggaran::find($tahunId);
        $tahunNumber = $tahun?->tahun ?? date('Y');

        return Excel::download(
            new SakipPerbandinganExport($tahunId),
            "Evaluasi_SAKIP_Kemenkumham_Kalsel_{$tahunNumber}.xlsx"
        );
    }

    /**
     * Export SAKIP Evaluation Sheet to PDF
     */
    public function exportSakipPdf(Request $request, int $satkerId)
    {
        $tahunId = $request->input('tahun_anggaran_id', TahunAnggaran::getAktif()?->id);

        $evaluasi = EvaluasiSakip::with(['satker', 'tahunAnggaran', 'creator'])
            ->where('satker_id', $satkerId)
            ->where('tahun_anggaran_id', $tahunId)
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.sakip_report', compact('evaluasi'))
            ->setPaper('a4', 'portrait');

        $safeKode = str_replace(['/', '\\'], '_', $evaluasi->satker->kode);

        return $pdf->download("LHE_SAKIP_{$safeKode}_{$evaluasi->tahunAnggaran->tahun}.pdf");
    }

    /**
     * Export Renaksi Achievement Report to PDF
     */
    public function exportRenaksiPdf(Request $request, int $id)
    {
        $renaksi = RencanaAksi::with(['satker', 'indikator', 'realisasi.buktiDukung', 'realisasi.verifikator', 'tahunAnggaran'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.renaksi_report', compact('renaksi'))
            ->setPaper('a4', 'portrait');

        $safeKode = str_replace(['/', '\\'], '_', $renaksi->satker->kode);

        return $pdf->download("Renaksi_{$safeKode}_{$renaksi->triwulan->value}_{$renaksi->tahunAnggaran->tahun}.pdf");
    }
}
