<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /* \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ], */
        \App\Events\VoteAttachedToLocation::class => [
            \App\Listeners\AttachVoteToLocationListener::class,
        ],
        \App\Events\QuestionClosed::class => [
            \App\Listeners\QuestionClosedListener::class,
        ],
    ];
}
