<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\Triwulan;

class RencanaAksi extends Model
{
    protected $table = 'rencana_aksi';

    protected $fillable = [
        'tahun_anggaran_id',
        'satker_id',
        'indikator_id',
        'nama_aksi',
        'triwulan',
        'target_output',
    ];

    protected function casts(): array
    {
        return [
            'triwulan' => Triwulan::class,
        ];
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(SatuanKerja::class, 'satker_id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(IndikatorKinerja::class, 'indikator_id');
    }

    public function realisasi(): HasOne
    {
        return $this->hasOne(RealisasiRenaksi::class);
    }
}
