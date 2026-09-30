<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferDetection extends Model
{
    protected $fillable = [
        'transfer_id',
        'category_id',
        'detection_technique',
        'rule_name',
        'matched_pattern',
        'matched_sample',
        'severity',
        'details',
    ];

    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }

    public function category()
    {
        return $this->belongsTo(SensitiveDataCategory::class, 'category_id');
    }
}
