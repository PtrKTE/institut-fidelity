<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operatrice extends Model
{
    protected $table = 'operatrices';
    public $timestamps = false;

    protected $fillable = ['nom', 'fonction'];

    public function facturePrestations(): HasMany
    {
        return $this->hasMany(FacturePrestation::class, 'operatrice_id');
    }
}
