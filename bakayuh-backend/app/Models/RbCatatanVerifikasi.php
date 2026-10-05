<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbCatatanVerifikasi extends Model
{
    public $timestamps = false;
    protected $table = 'rb_catatan_verifikasi';

    protected $fillable = [
        'rb_target_periode_id',
        'sender_id',
        'pesan',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function targetPeriode(): BelongsTo
    {
        return $this->belongsTo(RbTargetPeriode::class, 'rb_target_periode_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
