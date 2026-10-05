<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\AspekRb;

class RbIndikator extends Model
{
    protected $table = 'rb_indikator';

    protected $fillable = [
        'rb_area_id',
        'aspek',
        'kode',
        'nama_indikator',
        'keterangan_juknis',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'aspek' => AspekRb::class,
            'urutan' => 'integer',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(RbArea::class, 'rb_area_id');
    }

    public function subIndikator(): HasMany
    {
        return $this->hasMany(RbSubIndikator::class)->orderBy('nomor_poin');
    }
}
