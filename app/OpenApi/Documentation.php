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

class Documentation
{

}