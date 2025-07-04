<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;

class AssignUserRole
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
    public function handle(Registered $event): void
    {
        /** @disregard */
        $event->user->assignRole('user');
    }
}
