<?php

namespace App\Http\Controllers;

use App\Http\Requests\Client\UpdateClientRequest;
use App\Services\ClientService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    public function __construct(private ClientService $service)
    {
        
    }

    #[OA\Get(
        path: '/api/client/',
        operationId: 'listClients',
        summary: 'Lista clientes',
        description: "Retorna uma listagem paginada de clientes se o usuário for administrador e o próprio perfil de cliente se o usuário for cliente.",
        tags: ["Clients"],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listagem paginada de clientes ou o próprio perfil de cliente',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(
                            ref: '#/components/schemas/ClientPaginationResource'
                        ),
                        new OA\Schema(
                            ref: '#/components/schemas/ClientResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar listar clientes',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível listar clientes'
                        )
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        $clients = $this->service->index($user);
        if ($clients){
            return $clients;
        }

        return response()->json(["message" => "Não foi possivel listar clientes"], 500);
    }

    #[OA\Get(
        path: '/api/client/{id}/',
        operationId: 'showClient',
        summary: 'Exibe um unico cliente',
        description: "Exibe o cliente cujo o user pertence ao id passado por parâmetro.",
        tags: ['Clients'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Id do usuário cliente que será exibido',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 25)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retorna usuário cliente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ClientResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Sistema não encontra um usuário cliente correspondente ao id",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Usuário cliente não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Usuário não é dono do perfil de cliente e nem administrador",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não pode ver perfis que não sejam o seu'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar procurar por cliente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível realizar consulta de cliente'
                        )
                    ]
                )
            )
        ]
    )]
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $client = $this->service->show($user, $id);
        if ($client){
            return $client;
        }

        return response()->json(["message" => "Não foi possivel realizar consulta de cliente"]);
    }

    #[OA\Patch(
        path: '/api/client/{id}',
        operationId: 'updateClient',
        summary: 'Atualização de usuário cliente',
        description: "Permite a alteração das propriedades de usuários clientes.",
        tags: ['Clients'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Id do usuário cliente que será exibido',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 25)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Pelo menos um dado deve ser enviado para realizar a operação',
            content: new OA\JsonContent(
                required: ['name', 'password', 'phone', 'cep'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Joao Pedro'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'jp123!__zero123'
                    ),
                    new OA\Property(
                        property: 'cep',
                        type: 'string',
                        description: 'Código de endereçamento postal do usuário',
                        example: '12999999'
                    ),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        example: '12999999999'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Administrador ou dono do perfil consegue alterar suas propriedades',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/ClientResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Sistema não consegue encontrar usuário cliente pertencente ao id',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Cliente não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Usuário não é admin e nem responsável pelo perfil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não pode alterar perfis que não sejam o seu'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 503,
                description: 'API de consulta de cep indisponível',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Serviço de procura de cep indisponível'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'API de consulta de cep não encontra o cep passado pelo usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Cep não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar alterar cliente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível alterar propriedades de usuário cliente'
                        )
                    ]
                )
            )
        ]
    )]
    public function update(UpdateClientRequest $request, int $id)
    {
        $user = $request->user();
        $data = $request->validated();
        $client = $this->service->update($user, $data, $id);
        if ($client){
            return $client;
        }

        return response()->json(["message" => "Não foi possivel alterar propriedades de usuário cliente"], 500);
    }

    #[OA\Delete(
        path: '/api/client/{id}',
        operationId: 'deleteClient',
        summary: 'Exclui cliente',
        description: "Um usuário responsável pelo perfil ou admin pode deletá-lo",
        tags: ['Clients'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario consegue deletar perfil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Conta de cliente deletada'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Sistema não encontra um cliente correspondente ao id',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Cliente não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Usuário não é responsável pelo perfil encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não pode excluir perfis que não sejam o seu'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar deletar conta',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possivel deletar conta de cliente'
                        )
                    ]
                )
            )
        ]
    )]
    public function destroy(Request $request, int $id)
    {
        $user = $request->user();
        if ($this->service->destroy($user, $id)){
            return response()->json(["message" => "Conta de cliente deletada"], 200);
        }

        return response()->json(["message" => "Não foi possível deletar conta de cliente"], 500);
    }
}