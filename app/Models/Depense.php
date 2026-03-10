<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $connection = 'mysql_latin1';
    protected $table = 'depenses';
    public $timestamps = false;

    protected $fillable = [
        'agent_id', 'libelle', 'description',
        'quantite', 'montant',
    ];

    // 'total' is a GENERATED ALWAYS AS (quantite * montant) STORED column
    protected $guarded = ['total'];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
            'total' => 'decimal:2',
            'date_depense' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
