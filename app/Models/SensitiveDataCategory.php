<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensitiveDataCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'risk_level',
        'patterns',
        'is_active',
    ];

    protected $casts = [
        'patterns' => 'array',
        'is_active' => 'boolean',
    ];

    public function detections()
    {
        return $this->hasMany(TransferDetection::class, 'category_id');
    }
}
