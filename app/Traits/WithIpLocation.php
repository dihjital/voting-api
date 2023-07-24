<?php

namespace App\Traits;

use App\Jobs\GatherIpLocation;
use App\Models\Location;
use App\Events\VoteAttachedToLocation;
use Illuminate\Support\Facades\Log;

trait WithIpLocation
{
    protected $IpStackFunctionURL;
    protected $voteId; // TODO: Lehet ez a neve ennek a paraméternek ... ?

    public function initWithIpLocation($voteId): self 
    {
        $this->voteId = $voteId;
        $this->IpStackFunctionURL = self::getIpStackFunctionURL();
        return $this;
    }

    public static function getIpStackFunctionURL()
    {
        return env(
            'IPSTACK_FUNCTION_URL', 
            'https://faas-fra1-afec6ce7.doserverless.co/api/v1/web/fn-0bc28cb8-f671-491a-a17d-6d724af0f3fc/default/ipstack'
        );
    }

    public function gatherIpLocationIf($callback, $ipAddress)
    {
        $callback($ipAddress)
            ? $this->dispatchIpLocationGathering($ipAddress)
            : Log::error('IP address is invalid: '.$ipAddress);
    }

    protected function dispatchIpLocationGathering($ipAddress)
    {
        if ($location = Location::existsBasedOnIp($ipAddress)) { 
            // Location already exists but we still register this to capture the voter
            event(new VoteAttachedToLocation($location, $this->voteId));
        } else {
            dispatch(new GatherIpLocation($this->voteId, $this->IpStackFunctionURL, $ipAddress));
        }
    }
}