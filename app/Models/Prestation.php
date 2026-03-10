<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestation extends Model
{
    protected $table = 'prestations';
    public $timestamps = false;

    protected $fillable = ['libelle', 'tarif'];

    protected function casts(): array
    {
        return [
            'tarif' => 'decimal:2',
        ];
    }

    public function facturePrestations(): HasMany
    {
        return $this->hasMany(FacturePrestation::class, 'prestation_id');
    }
}
