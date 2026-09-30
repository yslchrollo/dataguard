<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'alert_uuid',
        'transfer_id',
        'user_id',
        'alert_type',
        'severity',
        'title',
        'description',
        'status',
        'assigned_to',
        'investigated_by',
        'investigation_notes',
        'action_taken',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function investigator()
    {
        return $this->belongsTo(User::class, 'investigated_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
