<?php

namespace App\Events;

use App\Models\Location;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteAttachedToLocation
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Location  $location
     * @param  int  $voteId
     * @return void
     */
    public function __construct(public Location $location, public int $voteId)
    {
        //
    }
}

