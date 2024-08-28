<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserLogin;

class LogSuccessfulLogin
{
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
    public function handle(object $event): void
    {
        // Ensure the event is an instance of the Login event
        if ($event instanceof Login) {
            $user = $event->user;

            UserLogin::create([
                'user_id' => $user->id,
                'login_time' => now(),
            ]);
        }
    }
}
