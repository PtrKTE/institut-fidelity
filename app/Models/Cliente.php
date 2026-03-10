<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cliente extends Model
{
    protected $table = 'clientes';
    public $timestamps = false;

    protected $fillable = [
        'nom', 'prenom', 'email', 'mot_de_passe', 'telephone',
        'date_anniversaire', 'cliente_depuis', 'lieu_enregistrement',
        'numero_carte', 'code_barres', 'taux_reduction',
        'enregistre_par', 'active',
    ];

    protected $hidden = ['mot_de_passe'];

    protected function casts(): array
    {
        return [
            'date_anniversaire' => 'date',
            'date_enregistrement' => 'datetime',
            'taux_reduction' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'client_id');
    }

    public function rendezvous(): HasMany
    {
        return $this->hasMany(Rendezvous::class, 'client_id');
    }

    public function commLogs(): HasMany
    {
        return $this->hasMany(CommLog::class, 'cliente_id');
    }

    public function enregistrePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }

    public function getFullNameAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}
