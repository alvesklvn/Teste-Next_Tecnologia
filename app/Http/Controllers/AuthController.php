<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
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
        summary: 'Registra um novo usuário no sistema',
        tags: ['Autenticação'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'phone', 'cep'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Kelvin'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'kelvin.pereira1904@gmail.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'S3nh4!um23'
                    ),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        format: 'phone',
                        example: '12999999999'
                    ),
                    new OA\Property(
                        property: 'cep',
                        type: 'string',
                        example: '11111111'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Verifique seu email'
            ),
            new OA\Response(
                response: 502,
                description: "Serviço de procura de cep indisponível"
            ),
            new OA\Response(
                response: 422,
                description: "Cep não encontrado"
            ),
            new OA\Response(
                response: 500,
                description: "Erro ao cadastrar usuário"
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
    }

    #[OA\Get(
        path: '/api/auth/email/verify/{id}/{hash}',
        summary: "Verifica o email de um usuário e o libera para utilizar o sistema",
        tags: ['Autenticação'],
        requestBody: null,
        responses: [
            new OA\Response(
                response: 200,
                description: "Email verificado com sucesso"
            ),
            new OA\Response(
                response: 400,
                description: "Hash de verificação de email inválido"
            )
        ]
    )]
    public function verify(int $id, string $hash)
    {
        if($this->service->verify($id, $hash)){
            return response()->json(["message" => "Email verificado com sucesso"], 200);
        }
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: "Verifica email e senha enviados pelo usuário e retorna um bearer token",
        tags: ['Autenticação'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'kelvin.pereira1904@gmail.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'S3nh4!um23'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token: {bearer token}"
            ),
            new OA\Response(
                response: 400,
                description: "Email e/ou senha inválidos"
            )
        ]
    )]
    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $token = $this->service->login($data);
        return response()->json(["Token" => "{$token}"], 200);
    }

    #[OA\Post(
        path: '/api/auth/logout',
        summary: "Invalida e deleta o bearer token e remove a autenticação do usuário",
        tags: ['Autenticação'],
        requestBody: null,
        responses: [
            new OA\Response(
                response: 200,
                description: "Token deletado"
            ),
            new OA\Response(
                response: 400,
                description: "Falha ao deletar token"
            )
        ]
    )]
    public function logout(Request $request)
    {
        try{
            $request->user()->currentAccessToken()->delete();
            return response()->json(["message" => "Token deletado"], 200);
        } catch (\Throwable $e){
            return response()->json(["message" => "Falha ao deletar token"], 400);
        }
    }
}