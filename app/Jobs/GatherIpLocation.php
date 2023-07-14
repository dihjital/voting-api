<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Events\VoteAttachedToLocation;

use App\Models\Location;

class GatherIpLocation extends Job
{
    private $vote_id;
    private $url;
    private $ipAddress;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($vote_id, $url, $ipAddress)
    {
        $this->vote_id = $vote_id;
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
        // TODO: Wait 3 seconds so I can test QUEUE_CONNECTION ... should be removed from PROD
        sleep(3);

        $response = Http::get($this->url, [
            'ip_address' => $this->ipAddress,
        ]);

        // Handle the responses
        if (!$response->successful()) {
            Log::error($response->status().":".$response->body());
               throw new \Exception($response->body());
        }

        $data = $response->json()['data'];

        $location = new Location();
        try {
            $location->fill([
                'ip' => $data['ip'],
                'country_name' => $data['country_name'],
                'city' => $data['city'],
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
            ]);
            $location->save();

            event(new VoteAttachedToLocation($location, $this->vote_id));
        } catch (\Exception $e) {
            Log::error(__('Failed to save location: ').$e->getMessage());
        }

    }
}
