<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Carbon\Constants\Format;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;

class AuthController extends Controller
{
    public function __construct(private AuthService $service)
    {
        
    }

    #[OA\Post(
        path: '/api/auth/register',
        operationId: 'createUser',
        tags: ['Auth'],
        summary: 'Registrar cliente',
        description: 'Permite que um usuário insira suas informações e se cadastre no sistema como cliente',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados necessários para realizar a operação',
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'cep', 'phone'],
                properties: [
                    new OA\Property(
                        property: 'name', 
                        type: 'string', 
                        description: 'nome do usuário', 
                        example: 'Kelvin'
                    ),
                    new OA\Property(
                        property: 'email', 
                        type: 'string', 
                        format: 'email', 
                        description: 'email do usuário', 
                        example: 'kelvin@gmail.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        description: 'Senha que o usuário utilizará para se autenticar no sistema',
                        example: 'S3nh4_um23!'
                    ),
                    new OA\Property(
                        property: 'cep',
                        type: 'string',
                        description: 'É o código de endereçamento postal do usuário',
                        example: '12999999'
                    ),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        description: 'Número de telefone celular do usuário',
                        example: '12999999999'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuário criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Verifique seu email'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 502,
                description: 'API de consulta de CEP indisponível',
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
                description: 'CEP não foi encontrado pela API',
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
                description: 'Erro interno na hora de registrar usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível registrar usuário'
                        )
                    ]
                )
            )
        ]
    )]
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = $this->service->register($data);
        if ($user){
            return response()->json(["message" => "Verifique seu email"], 201);
        }

        return response()->json(["message" => "Não foi possível registrar usuário"], 500);
    }

    #[OA\Get(
        path: '/api/auth/email/verify/{id}/{hash}',
        operationId: 'verifyEmail',
        tags: ['Auth'],
        summary: 'Verificar email de usuário',
        description: 'Permite que um usuário verifique o próprio email para ter acesso ao sistema',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Id do usuário que está verificando o próprio email',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 25)
            ),
            new OA\Parameter(
                name: 'hash',
                in: 'path',
                description: 'Hash sha1 do email do usuário',
                required: true,
                schema: new OA\Schema(type: 'string', example: 'abc1234k5h9')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sistema consegue verificar o email do usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Email verificado com sucesso'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Hash recebido pelo parametro da rota é inválido',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Hash de verificação de email inválido'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao tentar verificar email do usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível verificar email'
                        )
                    ]
                )
            ),
        ]
    )]
    public function verify(int $id, string $hash)
    {
        if($this->service->verify($id, $hash)){
            return response()->json(["message" => "Email verificado com sucesso"], 200);
        }

        return response()->json(["message" => "Não foi possível verificar email"], 500);
    }

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'authenticateUser',
        tags: ['Auth'],
        summary: 'Autenticar usuário',
        description: 'Gera e exibe bearer token que permite acesso a rotas protegidas pelo sanctum',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados necessários para realizar a operação',
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'kelvin@gmail.com',                        
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'S3nh4_um23!'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sistema gera o bearer token para o usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'bearerToken',
                            type: 'string',
                            example: '1|0wIXY0aDxFTGUSZ6PXEEVNwuCSAj3oPOlpjiq0MN6168fc8a'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Credenciais incorretas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Email e/ou senha inválidos'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao autenticar usuário',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Não foi possível autenticar usuário'
                        )
                    ]
                )
            )
        ]
    )]
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $token = $this->service->login($data);
        if ($token){
            return response()->json(["bearerToken" => "{$token}"], 200);
        }

        return response()->json(["message" => "Não foi possível autenticar usuário"], 500);
    }

    #[OA\Post(
        path: '/api/auth/logou',
        operationId: 'logoutUser',
        summary: 'Realiza logout',
        security: [['bearerAuth' => []]],
        description: 'Invalida e deleta o bearer token atual',
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout concluído',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Logout realizado com sucesso'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno ao deletar token',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Falha ao deletar token'
                        )
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($this->service->logout($user)){
            return response()->json(["message" => "Logout realizado com sucesso"], 200);
        }
        
        return response()->json(["message" => "Falha ao deletar token"], 500);
    }
}