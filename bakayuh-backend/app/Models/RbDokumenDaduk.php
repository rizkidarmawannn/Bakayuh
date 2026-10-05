<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RbDokumenDaduk extends Model
{
    public $timestamps = false;
    protected $table = 'rb_dokumen_daduk';

    protected $fillable = [
        'rb_target_periode_id',
        'nama_file',
        'path_file',
        'ukuran_file',
        'mime_type',
        'uploaded_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'ukuran_file' => 'integer',
        ];
    }

    public function targetPeriode(): BelongsTo
    {
        return $this->belongsTo(RbTargetPeriode::class, 'rb_target_periode_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUkuranFormatAttribute(): string
    {
        $kb = $this->ukuran_file / 1024;
        return $kb >= 1024 ? round($kb / 1024, 2) . ' MB' : round($kb, 1) . ' KB';
    }
}
