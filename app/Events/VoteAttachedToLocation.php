<?php

namespace App\Events;

use App\Models\Location;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteAttachedToLocation
{
    use Dispatchable, SerializesModels;

    public $location;
    public $voteId;

    /**
     * Create a new event instance.
     *
     * @param  Location  $location
     * @param  int  $voteId
     * @return void
     */
    public function __construct(Location $location, int $voteId)
    {
        $this->location = $location;
        $this->voteId = $voteId;
    }
}

