<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[OA\Schema(
        schema: 'AdminResource',
        properties: [
            new OA\Property(
                property: 'id',
                type: 'integer',
                example: 1
            ),
            new OA\Property(
                property: 'name',
                type: 'string',
                example: 'João Pedro'
            ),
            new OA\Property(
                property: 'email',
                type: 'string',
                example: 'jp05072007_@gmail.com'
            ),
            new OA\Property(
                property: 'is_barber',
                type: 'boolean',
                example: false
            )
        ]
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_barber' => $this->barber()->exists()
        ];
    }
}
