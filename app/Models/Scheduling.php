<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Scheduling extends Model
{
    protected $fillable = [
        'client_id',
        'barber_id',
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    protected function casts()
    {
        return [
            'id' => 'integer',
            'client_id' => 'integer',
            'barber_id' => 'integer',
            'start_date' => 'datetime:Y-m-d H:i:s',
            'end_date' => 'datetime:Y-m-d H:i:s',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s'
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }
}
