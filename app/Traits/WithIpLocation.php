<?php

namespace App\Traits;

use App\Jobs\GatherIpLocation;
use App\Models\Location;

trait WithIpLocation
{
    protected $ipstackUrl;

    public function initWithIpLocation(): self 
    {
        $this->ipstackUrl = 'https://faas-fra1-afec6ce7.doserverless.co/api/v1/web/fn-0bc28cb8-f671-491a-a17d-6d724af0f3fc/default/ipstack';
        return $this;
    }

    public function gatherIpLocation($ipAddress)
    {
        // TODO: Log error if an invalid IP address was provided ...
        if (self::isValidIpAddress($ipAddress) && !self::isLocationExists($ipAddress)) {
            dispatch(new GatherIpLocation($this->ipstackUrl, $ipAddress));
        }
    }
    
    protected static function isLocationExists($ipAddress): bool
    {
        try {
            Location::where('ip', $ipAddress)->firstOrFail();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    protected static function isValidIpAddress($ipAddress): bool
    {
        return filter_var($ipAddress, FILTER_VALIDATE_IP) !== false;
    }
}