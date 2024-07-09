<?php

namespace App\Events;

use App\Models\Location;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class VoteAttachedToLocation
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Location  $location
     * @param  int  $voteId
     * @return void
     */
    public function __construct(
        public Location $location, 
        public int $voteId)
    { }
}

