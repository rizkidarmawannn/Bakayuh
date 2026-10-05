<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiIku extends Model
{
    protected $table = 'realisasi_iku';

    protected $fillable = [
        'target_iku_id',
        'nilai_realisasi',
        'persentase_capaian',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_realisasi' => 'decimal:4',
            'persentase_capaian' => 'decimal:4',
        ];
    }

    public function targetIku(): BelongsTo
    {
        return $this->belongsTo(TargetIku::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusColorAttribute(): string
    {
        $p = (float) $this->persentase_capaian;
        return match (true) {
            $p >= 100 => 'green',
            $p >= 80 => 'yellow',
            default => 'red',
        };
    }
}
