<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class SchedulingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[OA\Schema(
        schema: 'SchedulingResource',
        properties: [
            new OA\Property(
                property: 'id',
                type: 'integer',
                example: 7
            ),
            new OA\Property(
                property: 'barber_id',
                type: 'integer',
                example: 2
            ),
            new OA\Property(
                property: 'client_id',
                type: 'integer',
                example: 15
            ),
            new OA\Property(
                property: 'start_date',
                type: 'string',
                format: 'date-time',
                example: '2026-10-30 07:10:00'
            ),
            new OA\Property(
                property: 'end_date',
                type: 'string',
                format: 'date-time',
                example: '2026-10-30 07:50:00'
            )
        ]
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_id' => $this->barber_id,
            'client_id' => $this->client_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}
