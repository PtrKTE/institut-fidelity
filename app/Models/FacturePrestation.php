<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacturePrestation extends Model
{
    protected $table = 'facture_prestations';
    public $timestamps = false;

    protected $fillable = [
        'facture_id', 'prestation_id', 'libelle', 'tarif',
        'quantite', 'montant', 'operatrice_id',
    ];

    protected function casts(): array
    {
        return [
            'tarif' => 'decimal:2',
            'montant' => 'decimal:2',
        ];
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class, 'prestation_id');
    }

    public function operatrice(): BelongsTo
    {
        return $this->belongsTo(Operatrice::class, 'operatrice_id');
    }
}
