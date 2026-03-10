<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rendezvous extends Model
{
    protected $table = 'rendezvous';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'client_id', 'nom_client', 'telephone', 'email',
        'date_rdv', 'heure_rdv', 'lieu', 'prestation',
        'agent_id', 'status', 'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'date_rdv' => 'date',
            'heure_rdv' => 'datetime:H:i',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'client_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
