<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationBancaire extends Model
{
    protected $table = 'operations_bancaires';
    public $timestamps = false;

    protected $fillable = [
        'agent_id', 'batch_id', 'type_operation', 'banque',
        'nom_operateur', 'montant_operation', 'date_operation',
    ];

    protected function casts(): array
    {
        return [
            'montant_operation' => 'decimal:2',
            'date_operation' => 'datetime',
            'date_enregistrement' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
