<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\TipeSatker;

class SatuanKerja extends Model
{
    protected $table = 'satuan_kerja';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => TipeSatker::class,
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'satker_id');
    }

    public function targetIku(): HasMany
    {
        return $this->hasMany(TargetIku::class, 'satker_id');
    }

    public function rencanaAksi(): HasMany
    {
        return $this->hasMany(RencanaAksi::class, 'satker_id');
    }

    public function evaluasiSakip(): HasMany
    {
        return $this->hasMany(EvaluasiSakip::class, 'satker_id');
    }

    public function rbTargetPeriode(): HasMany
    {
        return $this->hasMany(RbTargetPeriode::class, 'satker_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
