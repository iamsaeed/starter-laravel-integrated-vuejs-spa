<?php

namespace App\Listeners;

use App\Notifications\TemplatedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Send welcome email using the user_welcome template
        $event->user->notify(new TemplatedNotification('user_welcome', [
            'user' => $event->user,
        ]));
    }
}
