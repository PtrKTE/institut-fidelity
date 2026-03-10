<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommLog extends Model
{
    protected $table = 'comm_logs';
    public $timestamps = false;

    protected $fillable = [
        'cliente_id', 'type', 'message', 'date_envoi', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date_envoi' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
