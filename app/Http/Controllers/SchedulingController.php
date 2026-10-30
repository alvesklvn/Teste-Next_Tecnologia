<?php

namespace App\Http\Controllers;

use App\Http\Requests\Scheduling\ListSchedulingRequest;
use App\Http\Requests\Scheduling\StoreSchedulingRequest;
use App\Http\Requests\Scheduling\UpdateSchedulingRequest;
use App\Http\Resources\SchedulingResource;
use App\Models\Scheduling;
use App\Services\SchedulingService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

use function Laravel\Prompts\form;

class SchedulingController extends Controller
{
    public function __construct(private SchedulingService $service)
    {
        
    }

    #[OA\Get(
        path: '/api/scheduling/',
        operationId: 'listScheduling',
        summary: 'Lista agendamentos',
        description: "Retorna uma listagem paginada de agendamentos (10 por página). O usuário pode usar filtros de busca por query string ou pelo request body. Os filtros são: start_date, end_date, barber_id e client_id",
        tags: ["Schedulings"],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: "O campo client_id só pode ser usado por usuários clientes para ver os próprios agendamentos\n\nDados necessarios para realizar a operação",
            content: new OA\JsonContent(
                required: ['start_date', 'end_date', 'barber_id', 'client_id'],
                properties: [
                    new OA\Property(
                        property: 'start_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-10-30'
                    ),
                    new OA\Property(
                        property: 'end_date',
                        type: 'string',
                        format: 'date',
                        example: '2026-10-30'
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
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retorna a lista paginada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                ref: '#/components/schemas/SchedulingResource'
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
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar listar agendamentos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível listar os agendamentos'
                        )
                    ]
                )
            ),
        ]
    )]
    public function index(ListSchedulingRequest $request)
    {
        $filters = $request->validated();
        $user = $request->user();
        $list = $this->service->index($filters, $user);
        if ($list){
            return $list;
        }

        return response()->json(["message" => "Não foi possível listar os agendamentos"], 500);
    }

    #[OA\Post(
        path: '/api/scheduling/',
        operationId: 'createScheduling',
        summary: 'Cria um agendamento',
        description: "Permite a um usuário criar um novo agendamento.",
        tags: ['Schedulings'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Se um usuário for barbeiro, \"barber_id\" se torna opcional, se ele for apenas administrador, se torna obrigatório, e, se for cliente, \"client_id\" se torna proibído e \"barber_id\" obrigatório.\n\nDados necessários para realizar a operação:",
            content: new OA\JsonContent(
                required: ['start_date', 'end_date', 'barber_id', 'client_id'],
                properties: [
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
                        example: '2026-10-30 07:30:00'
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
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Agendamento é criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/SchedulingResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Entidade não verificada ou período de agendamento invalido/indisponível',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string'
                        )
                    ],
                    examples: [
                        new OA\Examples(
                            example: 'nao_verificado',
                            summary: 'Cliente e/ou barbeiro se classificam como entidade não verificada',
                            value: 'Barbeiro e/ou cliente não verificados'
                        ),
                        new OA\Examples(
                            example: 'indisponivel',
                            summary: 'Período de agendamento escolhido indisponível (madrugada ou ocupados)',
                            value: 'Período de tempo indisponível'
                        ),
                        new OA\Examples(
                            example: 'muito_longo',
                            summary: 'Período de agendamento muito longo (mais de uma hora e meia)',
                            value: 'Período de tempo muito longo'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar realizar criação de agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível criar agendamento'
                        )
                    ]
                )
            ),
        ]
    )]
    public function store(StoreSchedulingRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $scheduling = $this->service->store($data, $user);
        if ($scheduling){
            return new SchedulingResource($scheduling)->response()->setStatusCode(201);
        }

        return response()->json(["message" => "Não foi possivel criar agendamento"], 500);
    }

    #[OA\Get(
        path: '/api/scheduling/{id}/',
        operationId: 'showScheduling',
        summary: 'Exibe um unico agendamento',
        description: "Exibe o agendamento pertencente ao id passado por parâmetro.",
        tags: ['Schedulings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Id do agendamento que será exibido',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retorna agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/SchedulingResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Sistema não encontra um agendamento correspondente ao id",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Agendamento não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Usuário não é responsável pelo agendamento encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não é permitido de consultar esse agendamento'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar procurar por agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível realizar consulta de agendamento'
                        )
                    ]
                )
            )
        ]
    )]
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $scheduling = $this->service->show($id, $user);
        if ($scheduling){
            return $scheduling;
        }

        return response()->json(["message" => "Não foi possível realizar consulta de agendamento"], 500);
    }

    #[OA\Patch(
        path: '/api/scheduling/{id}',
        operationId: 'updateScheduling',
        summary: 'Atualização de agendamento',
        description: "Um usuário responsável pelo agendamento (cliente ou administradores) pode alterá-lo.",
        tags: ['Schedulings'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Id do agendamento que será alterado',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 10)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados que podem ser alterados pela operação',
            content: new OA\JsonContent(
                required: ['start_date', 'end_date', 'barber_id'],
                properties: [
                    new OA\Property(
                        property: 'start_date',
                        type: 'string',
                        format: 'date-time',
                        example: '2026-11-25 10:30:00'
                    ),
                    new OA\Property(
                        property: 'end_date',
                        type: 'string',
                        format: 'date-time',
                        example: '2026-11-25 11:00:00'
                    ),
                    new OA\Property(
                        property: 'barber_id',
                        type: 'integer',
                        example: 3
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário responsável consegue alterar agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/SchedulingResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Sistema não encontra um agendamento correspondente ao id',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Agendamento não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Usuário não é responsável pelo agendamento encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não é permitido de alterar esse agendamento'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Entidade não verificada ou período de agendamento invalido/indisponível',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string'
                        )
                    ],
                    examples: [
                        new OA\Examples(
                            example: 'nao_verificado',
                            summary: 'Cliente e/ou barbeiro se classificam como entidade não verificada',
                            value: 'Barbeiro e/ou cliente não verificados'
                        ),
                        new OA\Examples(
                            example: 'indisponivel',
                            summary: 'Período de agendamento escolhido indisponível (madrugada ou ocupados)',
                            value: 'Período de tempo indisponível'
                        ),
                        new OA\Examples(
                            example: 'muito_longo',
                            summary: 'Período de agendamento muito longo (mais de uma hora e meia)',
                            value: 'Período de tempo muito longo'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar alterar agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível alterar o agendamento'
                        )
                    ]
                )
            )
        ]
    )]
    public function update(UpdateSchedulingRequest $request, int $id)
    {
        $user = $request->user();
        $data = $request->validated();
        $scheduling = $this->service->update($data, $user, $id);
        if ($scheduling){
            return new SchedulingResource($scheduling);
        }

        return response()->json(["message" => "Não foi possivel alterar o agendamento"], 500);
    }

    #[OA\Delete(
        path: '/api/scheduling/{id}',
        operationId: 'deleteScheduling',
        summary: 'Exclui agendamento',
        description: "Um usuário responsável pelo agendamento cancelá-lo",
        tags: ['Schedulings'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario responsável consegue deletar agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Agendamento deletado com sucesso'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Sistema não encontra um agendamento correspondente ao id',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Agendamento não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Usuário não é responsável pelo agendamento encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não é permitido de alterar esse agendamento'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Agendamento não pode ser cancelado porque restam apenas 1 hora e meia',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não é possivel alterar esse agendamento pois faltam apenas uma hora e meia'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar alterar agendamento',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível deletar agendamento'
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
            return response()->json(["message" => "Agendamento deletado com sucesso."], 200);
        }

        return response()->json(["message" => "Não foi possivel deletar agendamento"], 500);
    }
}