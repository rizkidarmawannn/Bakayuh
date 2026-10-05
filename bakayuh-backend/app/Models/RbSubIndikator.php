<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RbSubIndikator extends Model
{
    protected $table = 'rb_sub_indikator';

    protected $fillable = [
        'rb_indikator_id',
        'nomor_poin',
        'judul_poin',
        'checklist_daduk',
        'catatan_tpi',
    ];

    protected function casts(): array
    {
        return [
            'nomor_poin' => 'integer',
        ];
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(RbIndikator::class, 'rb_indikator_id');
    }

    public function targetPeriode(): HasMany
    {
        return $this->hasMany(RbTargetPeriode::class);
    }
}
