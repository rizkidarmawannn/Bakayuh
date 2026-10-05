<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\StatusRenaksi;

class RealisasiRenaksi extends Model
{
    protected $table = 'realisasi_renaksi';

    protected $fillable = [
        'rencana_aksi_id',
        'deskripsi_realisasi',
        'persentase_selesai',
        'status',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
        'submitted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusRenaksi::class,
            'persentase_selesai' => 'integer',
            'verified_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function rencanaAksi(): BelongsTo
    {
        return $this->belongsTo(RencanaAksi::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function buktiDukung(): HasMany
    {
        return $this->hasMany(BuktiDukungRenaksi::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [StatusRenaksi::BelumLapor, StatusRenaksi::PerluPerbaikan]);
    }
}
