<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\VoteAttachedToLocation;
use Illuminate\Queue\Middleware\WithoutOverlapping;

use App\Models\Location;

class GatherIpLocation extends Job
{
    private $voteId;
    private $url;
    private $ipAddress;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($voteId, $url, $ipAddress)
    {
        $this->voteId = $voteId;
        $this->url = $url;
        $this->ipAddress = $ipAddress;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($location = self::isLocationExists($this->ipAddress)) {
            Log::info(__('Location already exists: ').$this->ipAddress);
            event(new VoteAttachedToLocation($location, $this->voteId));
        } else {
            $response = Http::get($this->url, [
                'ip_address' => $this->ipAddress,
            ]);

            // Handle the responses
            // TODO: Check returned data ...
            if (!$response->successful()) {
                Log::error($response->status().":".$response->body());
                throw new \Exception($response->body());
            }

            $data = $response->json()['data'];

            $location = new Location;
            try {
                $location->fill([
                    'ip' => $data['ip'],
                    'country_name' => $data['country_name'],
                    'city' => $data['city'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                ]);
                $location->save();

                event(new VoteAttachedToLocation($location, $this->voteId));
                // $location->votes()->attach($this->vote_id);
            } catch (\Exception $e) {
                Log::error(__('Failed to save location: ').$e->getMessage());
            }
        }

    }

    public function middleware()
    {
        return [(new WithoutOverlapping($this->ipAddress))->releaseAfter(15)->expireAfter(180)];
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
}
