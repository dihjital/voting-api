<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotification extends Job
{
    private $url;
    private $headers;
    private $payload;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($url, $headers, $payload)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withHeaders($this->headers)->post($this->url, $this->payload);

        // Handle the responses
        if (!$response->successful()) {
            Log::error($response->status().":".$response->body());
            throw new \Exception($response->body());
        }
    }

}
