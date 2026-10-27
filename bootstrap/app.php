<?php

use App\Exceptions\Cep\CepNaoEncontradoException;
use App\Exceptions\Cep\ViaCepIndisponivelException;
use App\Exceptions\EmailVerify\EmailAlreadyVerifiedException;
use App\Exceptions\EmailVerify\InvalidHashException;
use App\Exceptions\Login\InvalidCredentialsException;
use App\Exceptions\Register\RegisterErrorException;
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ViaCepIndisponivelException $e){
            return response()->json(["message" => "Serviço de procura de cep indisponível"], 502);
        });
        $exceptions->render(function (CepNaoEncontradoException $e){
            return response()->json(["message" => "Cep não encontrado"], 422);
        });
        $exceptions->render(function (RegisterErrorException $e){
            return response()->json(["message" => "Erro ao cadastrar usuário"], 500);
        });
        $exceptions->render(function (InvalidHashException $e){
            return response()->json(["message" => "Hash de verificação de email inválido"], 400);
        });
        $exceptions->render(function (InvalidCredentialsException $e){
            return response()->json(["message" => "Email e/ou senha inválidos"], 400);
        });
    })->create();
