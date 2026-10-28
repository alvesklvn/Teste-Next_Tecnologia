<?php

namespace App\Services;

use App\Exceptions\Admin\AdminNotFoundException;
use App\Exceptions\Admin\SoleAdminException;
use App\Exceptions\Register\RegisterErrorException;
use App\Http\Resources\AdminResource;
use App\Jobs\SendVerificationJob;
use App\Models\Barber;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function index()
    {
        try {
            return AdminResource::collection(User::query()->where('user_type_id', 1)->whereNotNull('email_verified_at')->paginate(10));
        } catch (\Throwable $e){
            return false;
        }
    }

    public function store(array $data)
    {
        try{
            $admin = DB::transaction(function () use ($data) {
                $admin = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'user_type_id' => 1,
                    'created_at' => now()
                ]);

                if ($data['is_barber']){
                    Barber::create([
                        'user_id' => $admin->id,
                        'phone' => $data['phone'],
                        'created_at' => now()
                    ]);
                }

                SendVerificationJob::dispatch($admin)->onQueue('verification')->beforeCommit();
                return $admin;
            });
            return $admin;
        } catch (\Throwable $e){
            return false;
        }     
    }

    public function show(int $id)
    {
        try{
            $admin = User::where('user_type_id', 1)->where('id', $id)->whereNotNull('email_verified_at')->first();
            if ($admin){
                return $admin;
            }
            throw new AdminNotFoundException();
        } catch (AdminNotFoundException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function update(User $admin, array $data)
    {
        try{
            if (isset($data['name'])){
                $admin->name = $data['name'];
            }

            if (isset($data['password'])){
                $admin->password = Hash::make($data['password']);
            }

            if($admin->barber()->exists()){
                if (isset($data['phone'])){
                    $admin->barber->phone = $data['phone'];
                    $admin->barber->save();
                }

                if (isset($data['is_barber']) && $data['is_barber'] === false){    
                    Barber::destroy($admin->barber->id);
                }
            } else {
                if (isset($data['is_barber']) && $data['is_barber']){
                    $admin->barber()->create([
                        'phone' => $data['phone']
                    ]);
                }
            }

            $admin->save();
            return $admin->refresh();
        } catch (\Throwable $e){
            return false;
        }
    }

    public function destroy(User $user)
    {
        $isSoleAdmin = !User::where('user_type_id', 1)->where('id', '!=', $user->id)->whereNotNull('email_verified_at')->exists();
        if ($isSoleAdmin){
            throw new SoleAdminException();
        } else {
            try{
                $user->delete();
                return true;
            } catch (\Throwable $e){
                return false;
            }
        }
    }
}