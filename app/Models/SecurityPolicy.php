<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityPolicy extends Model
{
    protected $fillable = [
        'policy_code',
        'name',
        'description',
        'policy_type',
        'action_on_violation',
        'severity',
        'rule_config',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'rule_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
