<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PredikatSakip;

class EvaluasiSakip extends Model
{
    protected $table = 'evaluasi_sakip';

    protected $fillable = [
        'tahun_anggaran_id',
        'satker_id',
        'nilai_perencanaan',
        'nilai_pengukuran',
        'nilai_pelaporan',
        'nilai_evaluasi',
        'nilai_total',
        'predikat',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_perencanaan' => 'decimal:2',
            'nilai_pengukuran' => 'decimal:2',
            'nilai_pelaporan' => 'decimal:2',
            'nilai_evaluasi' => 'decimal:2',
            'nilai_total' => 'decimal:2',
            'predikat' => PredikatSakip::class,
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hitungTotal(): float
    {
        return round(
            ((float)$this->nilai_perencanaan * 0.30) +
            ((float)$this->nilai_pengukuran  * 0.30) +
            ((float)$this->nilai_pelaporan   * 0.15) +
            ((float)$this->nilai_evaluasi    * 0.25), 2
        );
    }
}
