<?php

namespace App\Jobs;

use App\Enums\ActionType;
use App\Models\Scheduling;
use App\Models\User;
use App\Notifications\SchedulingNotification;
use App\Notifications\SchedulingUpdatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSchedulingNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(private User $user, private Scheduling $scheduling, private ActionType $action)
    {
        
    }
    
    public function backoff(): array
    {
        return [
            5, 10
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        match ($this->action) {
            ActionType::CREATED => $this->user->notify(
                new SchedulingNotification($this->scheduling)
            ),

            ActionType::UPDATED => $this->user->notify(
                new SchedulingUpdatedNotification($this->scheduling)
            )
    };
}
}
