<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\SatuanKerja;
use App\Models\EvaluasiSakip;

class SakipPerbandinganExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected int $tahunAnggaranId
    ) {}

    public function headings(): array
    {
        return [
            'No',
            'Kode Satker',
            'Nama Satuan Kerja',
            'Tipe',
            'Nilai Perencanaan (30%)',
            'Nilai Pengukuran (30%)',
            'Nilai Pelaporan (15%)',
            'Nilai Evaluasi (25%)',
            'Nilai Total SAKIP',
            'Predikat',
            'Kategori Predikat',
        ];
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        $satkers = SatuanKerja::where('is_active', true)->orderBy('tipe')->orderBy('nama')->get();
        $evaluasis = EvaluasiSakip::where('tahun_anggaran_id', $this->tahunAnggaranId)->get()->keyBy('satker_id');

        $rows = [];
        $no = 1;

        foreach ($satkers as $satker) {
            $eval = $evaluasis->get($satker->id);

            $rows[] = [
                $no++,
                $satker->kode,
                $satker->nama,
                strtoupper($satker->tipe->value),
                $eval ? (float)$eval->nilai_perencanaan : '-',
                $eval ? (float)$eval->nilai_pengukuran : '-',
                $eval ? (float)$eval->nilai_pelaporan : '-',
                $eval ? (float)$eval->nilai_evaluasi : '-',
                $eval ? (float)$eval->nilai_total : '-',
                $eval?->predikat?->value ?? '-',
                $eval?->predikat?->label() ?? '-',
            ];
        }

        return collect($rows);
    }
}
