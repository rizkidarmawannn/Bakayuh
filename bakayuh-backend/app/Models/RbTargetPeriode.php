<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\PeriodeRb;
use App\Enums\StatusVerifikasiRb;
use Carbon\Carbon;

class RbTargetPeriode extends Model
{
    protected $table = 'rb_target_periode';

    protected $fillable = [
        'rb_sub_indikator_id',
        'tahun_anggaran_id',
        'satker_id',
        'periode',
        'batas_waktu_upload',
        'status_verifikasi',
        'penjelasan_zi',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'periode' => PeriodeRb::class,
            'status_verifikasi' => StatusVerifikasiRb::class,
            'batas_waktu_upload' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function subIndikator(): BelongsTo
    {
        return $this->belongsTo(RbSubIndikator::class, 'rb_sub_indikator_id');
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(SatuanKerja::class, 'satker_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(RbDokumenDaduk::class)->orderBy('created_at', 'desc');
    }

    public function catatan(): HasMany
    {
        return $this->hasMany(RbCatatanVerifikasi::class)->orderBy('created_at', 'asc');
    }

    public function isLocked(): bool
    {
        return $this->status_verifikasi?->isLocked() ?? false;
    }

    public function getCountdownAttribute(): ?array
    {
        if (!$this->batas_waktu_upload) return null;
        $now = Carbon::now();
        $isPast = $now->gt($this->batas_waktu_upload);
        $diff = $now->diff($this->batas_waktu_upload);
        return [
            'days' => $diff->days,
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'is_past' => $isPast,
        ];
    }
}
