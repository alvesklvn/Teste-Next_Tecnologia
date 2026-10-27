<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SendVerificationJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user)
    {
        
    }

    public function backoff(): array
    {
        return [
            5, 10
        ];
    }

    public function uniqueid(): string
    {
        return "emailverify-{$this->user->id}";
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping("emailverifykey-{$this->user->id}"),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new Registered($this->user));
    }
}
