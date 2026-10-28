<?php

use App\Exceptions\Admin\AdminNotFoundException;
use App\Exceptions\Admin\SoleAdminException;
use App\Exceptions\Cep\CepNaoEncontradoException;
use App\Exceptions\Cep\ViaCepIndisponivelException;
use App\Exceptions\EmailVerify\EmailAlreadyVerifiedException;
use App\Exceptions\EmailVerify\InvalidHashException;
use App\Exceptions\Login\InvalidCredentialsException;
use App\Exceptions\Register\RegisterErrorException;
use App\Http\Middleware\IsAdminUserMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'is.Admin' => IsAdminUserMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ViaCepIndisponivelException $e){
            return response()->json(["message" => "Serviço de procura de cep indisponível"], 502);
        });
        $exceptions->render(function (CepNaoEncontradoException $e){
            return response()->json(["message" => "Cep não encontrado"], 422);
        });
        $exceptions->render(function (InvalidHashException $e){
            return response()->json(["message" => "Hash de verificação de email inválido"], 400);
        });
        $exceptions->render(function (InvalidCredentialsException $e){
            return response()->json(["message" => "Email e/ou senha inválidos"], 400);
        });
        $exceptions->render(function (SoleAdminException $e){
            return response()->json(["message" => "Você não pode apagar sua conta sendo o único administrador"], 422);
        });
        $exceptions->render(function (AdminNotFoundException $e){
            return response()->json(["message" => "Usuário administrador não encontrado"], 404);
        });
    })->create();
