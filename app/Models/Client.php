<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'adress',
        'city',
        'created_at',
        'updated_at'
    ];

    protected function casts()
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'phone' => 'string',
            'adress' => 'string',
            'city' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scheduling(): HasMany
    {
        return $this->hasMany(Scheduling::class);
    }
}
