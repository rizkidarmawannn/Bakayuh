<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAnggaran extends Model
{
    protected $table = 'tahun_anggaran';

    protected $fillable = [
        'tahun',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'is_aktif' => 'boolean',
        ];
    }

    public function targetIku(): HasMany
    {
        return $this->hasMany(TargetIku::class);
    }

    public function rencanaAksi(): HasMany
    {
        return $this->hasMany(RencanaAksi::class);
    }

    public function evaluasiSakip(): HasMany
    {
        return $this->hasMany(EvaluasiSakip::class);
    }

    public function rbTargetPeriode(): HasMany
    {
        return $this->hasMany(RbTargetPeriode::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public static function getAktif(): ?self
    {
        return static::aktif()->first();
    }
}
