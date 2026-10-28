<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\RegisterAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    public function __construct(private AdminService $service)
    {
        
    }
    
    #[OA\Get(
        path: '/api/admin/',
        operationId: 'listAdmins',
        summary: 'Lista administradores',
        description: "Retorna uma listagem paginada de administradores (10 por página).\n\n **Middleware:** Apenas admins (`is.Admin`)",
        tags: ["Admins"],
        security: [['bearerAuth' => []]],
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
                                ref: '#/components/schemas/AdminResource'
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
                description: 'Erro interno ao tentar listar administradores',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível listar administradores'
                        )
                    ]
                )
            ),
        ]
    )]
    public function index()
    {
        $list = $this->service->index();
        if ($list){
            return $list;
        }

        return response()->json(["message" => "Não foi possível listar administradores"], 500);
    }

    #[OA\Post(
        path: '/api/admin/',
        operationId: 'createAdmin',
        summary: 'Cria um admin',
        description: "Permite a um administrador criar um novo usuário administrador.\n\n**Middleware:** Apenas admins (`is.Admin`)",
        tags: ['Admins'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados necessários para realizar a operação ("phone" é necessário se "is_barber" for true)',
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'is_barber'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Joao Pedro'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'jp05072007@gmail.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'jp123!__zero123'
                    ),
                    new OA\Property(
                        property: 'is_barber',
                        type: 'boolean',
                        example: true
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
                response: 201,
                description: 'Usuário administrador é criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar registrar usuário administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível registrar usuário administrador'
                        )
                    ]
                )
            )
        ]
    )]
    public function store(RegisterAdminRequest $request)
    {
        $data = $request->validated();
        $admin = $this->service->store($data);

        if ($admin){
            return (new AdminResource($admin))->response()->setStatusCode(201);
        }

        return response()->json(["message" => "Não foi possível registrar usuário administrador"], 500);
    }

    #[OA\Get(
        path: '/api/admin/{id}/',
        operationId: 'showAdmin',
        summary: 'Exibe um unico administrador',
        description: "Exibe o administrador pertencente ao id passado por parâmetro.\n\n**Middleware:** Apenas admins (`is.Admin`)",
        tags: ['Admins'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Id do usuário administrador que será exibido',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 25)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retorna usuário admin',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Sistema não encontra um usuário administrador correspondente ao id",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Usuário administrador não encontrado'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar procurar por administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível procurar por usuário administrador'
                        )
                    ]
                )
            )
        ]
    )]
    public function show(int $id)
    {
        $admin = $this->service->show($id);
        if ($admin){
            return new AdminResource($admin);
        }
        return response()->json(["message" => "Não foi possível procurar por usuário administrador"], 500);

    }

    #[OA\Put(
        path: '/api/admin/',
        operationId: 'putUpdateAdmin',
        summary: 'Atualização de admin',
        description: "Um usuário administrador pode se auto modificar.\n\n**Middleware:** Apenas admins (`is.Admin`)",
        tags: ['Admins'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados necessários para realizar a operação ("phone" é necessário se "is_barber" for true)',
            content: new OA\JsonContent(
                required: ['name', 'password', 'is_barber'],
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
                        property: 'is_barber',
                        type: 'boolean',
                        example: true
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
                description: 'Administrador consegue atualizar suas propriedades',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar atualizar administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível atualizar usuário administrador'
                        )
                    ]
                )
            )
        ]
    )]
    #[OA\Patch(
        path: '/api/admin/',
        operationId: 'patchUpdateAdmin',
        summary: 'Atualização de admin',
        description: "Um usuário administrador pode se auto modificar.\n\n**Middleware:** Apenas admins (`is.Admin`)",
        tags: ['Admins'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Pelo menos um dado deve ser enviado para realizar a operação ("phone" é necessário se "is_barber" for true)',
            content: new OA\JsonContent(
                required: ['name', 'password', 'is_barber'],
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
                        property: 'is_barber',
                        type: 'boolean',
                        example: true
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
                description: 'Administrador consegue atualizar suas propriedades',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/AdminResource'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar atualizar administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível atualizar usuário administrador'
                        )
                    ]
                )
            )
        ]
    )]
    public function update(UpdateAdminRequest $request)
    {
        $admin = $request->user();
        $data = $request->validated();
        $updatedAdmin = $this->service->update($admin, $data);
        if ($updatedAdmin){
            return new AdminResource($updatedAdmin);
        }
        return response()->json(["message" => "Não foi possível atualizar usuário administrador"], 500);
    }

    #[OA\Delete(
        path: '/api/admin/',
        operationId: 'deleteAdmin',
        summary: 'Exclusão de admin',
        description: "Um usuário administrador pode deletar sua conta do sistema (se não for o único administrador).\n\n**Middleware:** Apenas admins (`is.Admin`)",
        tags: ['Admins'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuario administrador consegue deletar a própria conta',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você deletou sua conta de administrador'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Administrador tenta excluir sua conta do sistema sendo o único administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Você não pode apagar sua conta sendo o único administrador'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar excluir conta de administrador',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível deletar conta de administrador'
                        )
                    ]
                )
            ),
        ]
    )]
    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($this->service->destroy($user)){
            return response()->json(["message" => "Você deletou sua conta de administrador"], 200);
        }

        return response()->json(["message" => "Não foi possivel deletar conta de administrador"], 500);
    }
}