<?php

namespace App\Services;

use App\Enums\ActionType;
use App\Exceptions\Scheduling\CancellationWindowExpiredException;
use App\Exceptions\UnverifiedEntityException;
use App\Http\Resources\SchedulingResource;
use App\Jobs\SendSchedulingNotificationJob;
use App\Models\Barber;
use App\Models\Client;
use App\Models\Scheduling;
use App\Models\User;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidPeriodDateException;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class SchedulingService
{
    public function index(array $filters, User $user)
    {
        try{
            $isAdmin = $user->userType->title === "admin";

            $query = Scheduling::query()->select(
                'id',
                'start_date',
                'end_date',
                'barber_id',
            );

            if ($isAdmin){
                $query->addSelect('client_id');
                if (isset($filters['client_id'])){
                    $query->where('client_id', $filters['client_id']);
                }
            } else {
                if (isset($filters['client_id'])){
                    $query->addSelect('client_id');
                    $query->where('client_id', $user->client->id);
                }
            }

            if (isset($filters['barber_id'])){
                $query->where('barber_id', $filters['barber_id']);
            }

            if (isset($filters['start_date'])){
                $query->whereDate('start_date', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])){
                $query->whereDate('end_date', '<=', $filters['end_date']);
            }

            return $query->orderBy('start_date')->paginate(10)->withQueryString();
        } catch (\Throwable $e){
            return false;
        }
    }

    public function store(array $data, User $user)
    {
        try{
            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);

            if (!isset($data['barber_id'])){
                $data['barber_id'] = $user->barber->id;
            }

            if (!isset($data['client_id'])){
                $data['client_id'] = $user->client->id;
            }

            $client = Client::where('id', $data['client_id'])->first();
            $barber = Barber::where('id', $data['barber_id'])->first();

            if (!$barber->user->email_verified_at || !$client->user->email_verified_at){
                throw new UnverifiedEntityException();
            }            

            if ($this->barberOrClientAreBusy($barber, $client, $start, $end) || $this->isOutsideBusinessHours($start, $end)){
                throw new InvalidPeriodDateException("Período de tempo indisponível", 422);
            }

            if ($start->diffInMinutes($end) > 90){
                throw new InvalidPeriodDateException("Período de tempo muito longo", 422);
            }

            $scheduling = Scheduling::create([
                'barber_id' => $data['barber_id'],
                'client_id' => $data['client_id'],
                'start_date' => $start,
                'end_date' => $end
            ]);
            SendSchedulingNotificationJob::dispatch($barber->user, $scheduling, ActionType::CREATED)->onQueue('schedulings');
            return $scheduling;
        } catch (UnverifiedEntityException $e){
            throw $e;
        } catch (InvalidPeriodDateException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function show(int $id, User $user)
    {
        try{
            $scheduling = Scheduling::where('id', $id)->first();
            if (!$scheduling){
                throw new NotFoundResourceException("Agendamento não encontrado", 404);
            }
            $isAdminOrOwner = $user->userType->title === "admin" || $scheduling->client_id === $user->client->id;

            if ($isAdminOrOwner){
                return new SchedulingResource($scheduling);
            }

            throw new UnauthorizedException("Você não é permitido de consultar esse agendamento", 403);
        } catch (NotFoundResourceException $e){
            throw $e;
        } catch (UnauthorizedException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function update(array $data, User $user, int $id)
    {
        try{
            $scheduling = Scheduling::where('id', $id)->first();
            if (!$scheduling){
                throw new NotFoundResourceException("Agendamento não encontrado", 404);
            }
            $isAdmin = $user->userType->title === "admin";
            $isOwner = $scheduling->client_id === $user->client?->id;

            if (!$isAdmin && !$isOwner){
                throw new UnauthorizedException("Você não é permitido de alterar esse agendamento", 403);
            }

            if (now()->diffInMinutes($scheduling->start_date) <= 90){
                throw new CancellationWindowExpiredException();
            }

            $start = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::parse($scheduling->start_date);

            $end = isset($data['end_date']) ? Carbon::parse($data['end_date']) : Carbon::parse($scheduling->end_date);

            if ($start >= $end){
                throw new InvalidPeriodDateException("Período de tempo indisponível", 422);
            }

            $barber = isset($data['barber_id']) ? Barber::where('id', $data['barber_id'])->first() : $scheduling->barber;

            $client = $scheduling->client;

            if (!$barber->user->email_verified_at){
                throw new UnverifiedEntityException();
            }            

            if ($this->barberOrClientAreBusy($barber, $client, $start, $end, $scheduling) || $this->isOutsideBusinessHours($start, $end)){
                throw new InvalidPeriodDateException("Período de tempo indisponível", 422);
            }

            if ($start->diffInMinutes($end) > 90){
                throw new InvalidPeriodDateException("Período de tempo muito longo", 422);
            }

            $scheduling->update([
                'start_date' => $start,
                'end_date' => $end,
                'barber_id' => $barber->id
            ]);
            SendSchedulingNotificationJob::dispatch($barber->user, $scheduling, ActionType::UPDATED)->onQueue('schedulings');
            return $scheduling;
        } catch (NotFoundResourceException $e){
            throw $e;
        } catch (UnauthorizedException $e){
            throw $e;
        } catch (CancellationWindowExpiredException $e){
            abort(500, now()->diffInMinutes($scheduling->start_date));
            throw $e;
        } catch (UnverifiedEntityException $e){
            throw $e;
        } catch (InvalidPeriodDateException $e){
            throw $e;
        } catch (\Throwable $e){
            return false;
        }
    }

    public function destroy(User $user, int $id)
    {
        try {
            $scheduling = Scheduling::where('id', $id)->first();
            if (!$scheduling){
                throw new NotFoundResourceException("Agendamento não encontrado", 404);
            }

            $isAdmin = $user->userType->title === "admin";
            $isOwner = $scheduling->client_id === $user->client?->id;

            if (!$isAdmin && !$isOwner){
                throw new UnauthorizedException("Você não é permitido de alterar esse agendamento", 403);
            } 

            if (now()->diffInMinutes($scheduling->start_date) <= 90){
                throw new CancellationWindowExpiredException();
            }

            $scheduling->delete();
            return true;
        } catch (NotFoundResourceException $e){
            throw $e;
        } catch (UnauthorizedException $e){
            throw $e;
        } catch (CancellationWindowExpiredException $e) {
            throw $e;  
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function barberOrClientAreBusy(Barber $barber, Client $client, Carbon $start, Carbon $end, ?Scheduling $scheduling = null): bool
    {
        $clientIsBusy = Scheduling::where('client_id', $client->id)->where('start_date', '<', $end)->where('end_date', '>', $start);

        $barberIsBusy = Scheduling::where('barber_id', $barber->id)->where('start_date', '<', $end)->where('end_date', '>', $start);
        if ($scheduling){
            $clientIsBusy = $clientIsBusy->where('id', '!=', $scheduling->id);

            $barberIsBusy = $barberIsBusy->where('id', '!=', $scheduling->id);
        }

        return $barberIsBusy->exists() || $clientIsBusy->exists();
    }

    private function isOutsideBusinessHours(Carbon $start, Carbon $end): bool
    {
        $open = $start->copy()->setTime(7, 0);
        $close = $start->copy()->addDay()->startOfDay();

        return $start->lessThan($open) || $end->greaterThan($close);
    }
}