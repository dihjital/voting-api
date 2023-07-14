<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\VoteAttachedToLocation;

class AttachVoteToLocationListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  VoteAttachedToLocation  $event
     * @return void
     */
    public function handle(VoteAttachedToLocation $event)
    {
        $location = $event->location;
        $voteId = $event->voteId;

        $location->votes()->attach($voteId);
    }
}
