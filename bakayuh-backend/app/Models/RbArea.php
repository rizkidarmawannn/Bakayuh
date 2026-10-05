<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\KategoriRb;
use App\Enums\KomponenRb;

class RbArea extends Model
{
    protected $table = 'rb_area';

    protected $fillable = [
        'kategori',
        'komponen',
        'kode',
        'nama_area',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'kategori' => KategoriRb::class,
            'komponen' => KomponenRb::class,
            'urutan' => 'integer',
        ];
    }

    public function indikator(): HasMany
    {
        return $this->hasMany(RbIndikator::class)->orderBy('urutan');
    }
}
