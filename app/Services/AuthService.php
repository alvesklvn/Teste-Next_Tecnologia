<?php

namespace App\Services;

use App\Exceptions\Cep\CepNotFoundException;
use App\Exceptions\Cep\ViaCepUnavailableException;
use App\Exceptions\EmailVerify\InvalidHashException;
use App\Exceptions\Login\InvalidCredentialsException;
use App\Jobs\SendVerificationJob;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthService
{
    private const DUMMY_HASH = "\$2y\$12\$wbmJvsD2li.HbM25e.IKyuI5wdcE0IPYBj4dEdhA63PyA.EKW35T.";

    public function register(array $data)
    {
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

        try{
            $user = DB::transaction(function () use ($data, $response) {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'user_type_id' => 2,
                    'created_at' => now()
                ]);

                Client::create([
                    'user_id' => $user->id,
                    'phone' => $data['phone'],
                    'address' => $response->json()['logradouro'],
                    'city' => $response->json()['localidade'] . ' - ' . $response->json()['uf'],
                    'created_at' => now()
                ]);
                SendVerificationJob::dispatch($user)->onQueue('verification')->beforeCommit();
                return $user;
            });
            return $user;
        } catch (ViaCepUnavailableException $e){
            throw $e;
        } catch (CepNotFoundException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function verify(int $id, string $hash)
    {
        try {
            $user = User::findOrFail($id);
            
            if (!hash_equals(sha1($user->getEmailForVerification()), $hash)){
                throw new InvalidHashException();
            }
        
            $user->markEmailAsVerified();
            return true;
        } catch (InvalidHashException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function login(array $data)
    {
        try{
            $user = User::whereNotNull('email_verified_at')->where('email', $data['email'])->first();
            $hashed = $user ? $user->password : self::DUMMY_HASH;

            $passwordIsCorrect = Hash::check($data['password'], $hashed);
            if (!$passwordIsCorrect || !$user){
                throw new InvalidCredentialsException();
            }

            return $user->createToken('login')->plainTextToken;
        } catch (InvalidCredentialsException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function logout(User $user)
    {
        try{
            $user->currentAccessToken()->delete();
            return true;
        } catch (\Throwable $e){
            return false;
        }
    }
}