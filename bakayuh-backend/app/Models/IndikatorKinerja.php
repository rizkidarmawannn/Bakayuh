<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PolaritasIku;
use App\Enums\LevelIku;

class IndikatorKinerja extends Model
{
    protected $table = 'indikator_kinerja';

    protected $fillable = [
        'kode',
        'nama',
        'satuan',
        'polaritas',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'polaritas' => PolaritasIku::class,
            'level' => LevelIku::class,
        ];
    }

    public function targetIku(): HasMany
    {
        return $this->hasMany(TargetIku::class, 'indikator_id');
    }

    public function rencanaAksi(): HasMany
    {
        return $this->hasMany(RencanaAksi::class, 'indikator_id');
    }
}
