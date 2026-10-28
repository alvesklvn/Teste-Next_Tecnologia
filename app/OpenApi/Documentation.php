<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Barbearia',
    version: '1.0.0',
    description: 'API para gerenciamento de uma barbearia'
)]

#[OA\Server(
    url: 'http://127.0.0.1:8000'
)]

#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    description: 'Token de autenticação do laravel sanctum'
)]

#[OA\Schema(
    schema: 'LaravelPaginationMeta',
    title: 'Metadados da paginação',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'current_page',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'from',
            type: 'integer',
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'last_page',
            type: 'integer',
            example: 1
        ),
        new OA\Property(
            property: 'links',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: 'url',
                        type: 'string',
                        example: "http://localhost:8000/api/admin?page=1"
                    ),
                    new OA\Property(
                        property: 'label',
                        type: 'string',
                        example: "1"
                    ),
                    new OA\Property(
                        property: 'page',
                        type: 'integer',
                        example: 1
                    ),
                    new OA\Property(
                        property: 'active',
                        type: 'boolean',
                        example: true
                    )
                ]
            )
        ),
        new OA\Property(
            property: 'path',
            type: 'string',
            example: 'http://localhost:8000/api/admin',
        ),
        new OA\Property(
            property: 'per_page',
            type: 'integer',
            example: 10
        ),
        new OA\Property(
            property: 'to',
            type: 'integer',
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: 'total',
            type: 'integer',
            example: 1
        )
    ]
)]

#[OA\Schema(
    schema: 'LaravelPaginationLinks',
    title: 'Links de paginação',
    type: 'object',
    properties: [
        new OA\Property(
            property: 'first',
            type: 'string',
            example: 'http://localhost:8000/api/admin?page=1'
        ),
        new OA\Property(
            property: 'last',
            type: 'string',
            example: 'http://localhost:8000/api/admin?page=1'
        ),
        new OA\Property(
            property: 'prev',
            type: 'string',
            example: null,
            nullable: true
        ),
        new OA\Property(
            property: 'next',
            type: 'string',
            example: null,
            nullable: true
        )
    ]

)]
class Documentation
{

}