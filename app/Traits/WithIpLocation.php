<?php

namespace App\Traits;

use App\Jobs\GatherIpLocation;
use App\Models\Location;
use App\Events\VoteAttachedToLocation;

trait WithIpLocation
{
    protected $ipstackUrl;
    protected $voteId; // TODO: Lehet ez a neve ennek a paraméternek ... ?

    public function initWithIpLocation($voteId): self 
    {
        $this->voteId = $voteId;
        $this->ipstackUrl = 'https://faas-fra1-afec6ce7.doserverless.co/api/v1/web/fn-0bc28cb8-f671-491a-a17d-6d724af0f3fc/default/ipstack';
        return $this;
    }

    public function gatherIpLocation($ipAddress)
    {
        // TODO: Log error if an invalid IP address was provided ...
        if (self::isValidIpAddress($ipAddress)) {
            if ($location = self::isLocationExists($ipAddress)) { // Location already exists but we still register this to capture the voter
                event(new VoteAttachedToLocation($location, $this->voteId));
                // $location->votes()->attach($this->vote_id);
            } else { 
                dispatch(new GatherIpLocation($this->voteId, $this->ipstackUrl, $ipAddress));
            }
        }
    }
    
    protected static function isLocationExists($ipAddress)
    {
        try {
            $location = Location::where('ip', $ipAddress)->firstOrFail();
        } catch (\Exception $e) {
            return false;
        }
        return $location;
    }

    protected static function isValidIpAddress($ipAddress): bool
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP) !== false;
    }
}