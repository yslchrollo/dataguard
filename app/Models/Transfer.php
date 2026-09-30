<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'transfer_uuid',
        'user_id',
        'file_name',
        'original_file_name',
        'file_path',
        'file_type',
        'file_extension',
        'file_size_bytes',
        'destination',
        'destination_type',
        'purpose',
        'description',
        'extracted_text_sample',
        'decision',
        'risk_score',
        'risk_level',
        'decision_reason',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'file_size_bytes' => 'integer',
        'risk_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detections()
    {
        return $this->hasMany(TransferDetection::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function isBlocked(): bool
    {
        return $this->decision === 'blocked';
    }

    public function isFlagged(): bool
    {
        return $this->decision === 'flagged';
    }

    public function isAllowed(): bool
    {
        return $this->decision === 'allowed';
    }
}
