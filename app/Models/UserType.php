<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class UserType extends Model
{
    protected $fillable = [
        'title',
        'created_at',
        'updated_at'
    ];

    protected function casts()
    {
        return [
            'id' => 'integer',
            'title' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
