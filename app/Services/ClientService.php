<?php

namespace App\Services;

use App\Exceptions\Cep\CepNotFoundException;
use App\Exceptions\Cep\ViaCepUnavailableException;
use App\Http\Resources\ClientResource;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ClientService
{
    public function index(User $user)
    {
        try{
            if ($user->userType->title === 'admin'){
                return ClientResource::collection(User::query()->where('user_type_id', 2)->whereNotNull('email_verified_at')->paginate(10));
            }

            return new ClientResource($user);
        } catch (\Throwable) {
            return false;
        }
    }

    public function show(User $user, int $id)
    {
        try{
            $client = User::where('user_type_id', 2)->where('id', $id)->whereNotNull('email_verified_at')->first();
            if (!$client){
                throw new NotFoundResourceException("Usuário cliente não encontrado", 404);
            }

            $isAdminOrClient = $user->userType->title === "admin" || $client->id === $user->id;

            if (!$isAdminOrClient){
                throw new UnauthorizedException("Você não pode ver perfis que não sejam o seu", 403);
            }

            return new ClientResource($client);
            
        } catch (NotFoundResourceException $e){
            throw $e;
        } catch (UnauthorizedException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function update(User $user, array $data, int $id)
    {
        try{
            $client = User::where('user_type_id', 2)->whereNotNull('email_verified_at')->where('id', $id)->first();
            if (!$client){
                throw new NotFoundResourceException("Cliente não encontrado", 404);
            }

            $isAdminOrClient = $user->userType->title === "admin" || $client->id === $user->id;

            if (!$isAdminOrClient){
                throw new UnauthorizedException("Você não pode alterar perfis que não sejam o seu", 403);
            }

            if (isset($data['name'])){
                $client->name = $data['name'];
            }

            if (isset($data['password'])){
                $client->password = Hash::make($data['password']);
            }

            if (isset($data['phone'])){
                $client->client->phone = $data['phone'];
            }

            if (isset($data['cep'])){
                try {
                    $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$data['cep']}/json/");
                } catch (ConnectionException $e){
                    throw new ViaCepUnavailableException();
                }

                if ($response->failed()){
                    throw new ViaCepUnavailableException();
                }

                if ($response->json()['erro'] ?? false){
                    throw new CepNotFoundException();
                }

                $client->client->city = $response->json()['localidade'] . ' - ' . $response->json()['uf'];
                $client->client->address = $response->json()['logradouro'];
            }

            $client->save();
            $client->client->save();
            return new ClientResource($client->refresh());
        } catch (NotFoundResourceException $e){
            throw $e;
        } catch (UnauthorizedException $e){
            throw $e;
        } catch (ViaCepUnavailableException $e){
            throw $e;
        } catch (CepNotFoundException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function destroy(User $user, int $id)
    {
        $client = User::where('user_type_id', 2)->whereNotNull('email_verified_at')->where('id', $id)->first();
        if (!$client){
            throw new NotFoundResourceException("Cliente não encontrado", 404);
        }

        $isAdminOrClient = $user->userType->title === "admin" || $client->id === $user->id;

        if (!$isAdminOrClient){
            throw new UnauthorizedException("Você não pode excluir perfis que não sejam o seu", 403);
        }

        try{
            $client->delete();
            return true;
        } catch (\Throwable){
            return false;
        }
    }
}