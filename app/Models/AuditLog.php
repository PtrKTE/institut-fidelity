<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id',
        'route', 'http_method', 'details', 'ip_address',
        'user_agent', 'success', 'before_state', 'after_state',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'success' => 'boolean',
            'before_state' => 'json',
            'after_state' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
