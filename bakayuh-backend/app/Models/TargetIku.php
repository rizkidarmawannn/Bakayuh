<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TargetIku extends Model
{
    protected $table = 'target_iku';

    protected $fillable = [
        'tahun_anggaran_id',
        'satker_id',
        'indikator_id',
        'nilai_target',
    ];

    protected function casts(): array
    {
        return [
            'nilai_target' => 'decimal:4',
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
        return $this->hasOne(RealisasiIku::class);
    }
}
