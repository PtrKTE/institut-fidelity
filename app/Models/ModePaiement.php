<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModePaiement extends Model
{
    protected $table = 'modes_paiement';
    public $timestamps = false;

    protected $fillable = ['nom'];
}
