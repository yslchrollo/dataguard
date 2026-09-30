<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected $casts = [
        'details' => 'array', 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, string $module, string $description, ?array $details = null, $user = null): self
    {
        $userId = null;
        if ($user) {
            $userId = is_object($user) ? $user->id : $user;
        } elseif (auth()->check()) {
            $userId = auth()->id();
        }

        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'System / Console',
            'details' => $details,
        ]);
    }
}
