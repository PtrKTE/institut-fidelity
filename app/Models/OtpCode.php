<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $table = 'otp_codes';
    public $timestamps = false;

    protected $fillable = [
        'email', 'code', 'context', 'created_at', 'used',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'used' => 'boolean',
        ];
    }
}
