<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'prenom', 'email', 'username', 'role',
        'mot_de_passe', 'lieu_affecte', 'session_token',
    ];

    protected $hidden = [
        'mot_de_passe', 'session_token',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->mot_de_passe;
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'caissiere_id');
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class, 'agent_id');
    }

    public function operationsBancaires(): HasMany
    {
        return $this->hasMany(OperationBancaire::class, 'agent_id');
    }

    public function rendezvous(): HasMany
    {
        return $this->hasMany(Rendezvous::class, 'agent_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
