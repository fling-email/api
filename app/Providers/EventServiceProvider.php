<?php

declare(strict_types=1);

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use App\Events\Event;
use App\Listeners\Listener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     * @phan-var array<class-string<Event>, list<class-string<Listener>>>
     */
    protected $listen = [
        //
    ];
}
