<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\SatuanKerja;
use App\Models\IndikatorKinerja;
use App\Models\TargetIku;

class IkuMatriksExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected int $tahunAnggaranId
    ) {}

    public function headings(): array
    {
        $headers = ['Kode Satker', 'Nama Satuan Kerja', 'Tipe'];
        $indikators = IndikatorKinerja::orderBy('kode')->get();

        foreach ($indikators as $ind) {
            $headers[] = "[{$ind->kode}] Target";
            $headers[] = "[{$ind->kode}] Realisasi";
            $headers[] = "[{$ind->kode}] Capaian (%)";
        }

        return $headers;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        $satkers = SatuanKerja::where('is_active', true)->orderBy('tipe')->orderBy('nama')->get();
        $indikators = IndikatorKinerja::orderBy('kode')->get();

        $targets = TargetIku::with('realisasi')
            ->where('tahun_anggaran_id', $this->tahunAnggaranId)
            ->get();

        $matrix = [];

        foreach ($satkers as $satker) {
            $row = [
                $satker->kode,
                $satker->nama,
                strtoupper($satker->tipe->value),
            ];

            foreach ($indikators as $ind) {
                $target = $targets->first(fn ($t) => $t->satker_id === $satker->id && $t->indikator_id === $ind->id);
                $row[] = $target ? (float)$target->nilai_target : '-';
                $row[] = $target?->realisasi ? (float)$target->realisasi->nilai_realisasi : '-';
                $row[] = $target?->realisasi ? (float)$target->realisasi->persentase_capaian . '%' : '-';
            }

            $matrix[] = $row;
        }

        return collect($matrix);
    }
}
