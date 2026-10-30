<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenAPi\Attributes as OA;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[OA\Schema(
        schema: 'ClientResource',
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
                property: 'phone',
                type: 'string',
                example: "12999999999"
            ),
            new OA\Property(
                property: 'address',
                type: 'string',
                example: "Rua Doutor Celestino",
            ),
            new OA\Property(
                property: 'city',
                type: 'string',
                example: 'Cruzeiro - SP'
            )
        ]
    )]

    #[OA\Schema(
        schema: 'ClientPaginationResource',
        properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(
                    ref: '#/components/schemas/ClientResource'
                )
            ),
            new OA\Property(
                property: 'links',
                ref: '#/components/schemas/LaravelPaginationLinks'          
            ),
            new OA\Property(
                property: 'meta',
                ref: '#/components/schemas/LaravelPaginationMeta'
            )
        ]
    )]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->client->phone,
            'address' => $this->client->address,
            'city' => $this->client->city
        ];
    }
}