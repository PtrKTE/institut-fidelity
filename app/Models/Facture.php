<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $table = 'factures';
    public $timestamps = false;

    protected $fillable = [
        'client_id', 'nom_client', 'telephone_client', 'date_facture',
        'montant_total', 'taux_remise', 'montant_remise', 'montant_net',
        'mode_paiement', 'lieu_prestation', 'caissiere_id',
    ];

    protected function casts(): array
    {
        return [
            'date_facture' => 'datetime',
            'montant_total' => 'decimal:2',
            'montant_remise' => 'decimal:2',
            'montant_net' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'client_id');
    }

    public function caissiere(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caissiere_id');
    }

    public function prestations(): HasMany
    {
        return $this->hasMany(FacturePrestation::class, 'facture_id');
    }
}
