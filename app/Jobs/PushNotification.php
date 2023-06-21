<?php

namespace App\Jobs;

class ExampleJob extends Job
{
    private $option;
    private $ch;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($option)
    {
        // Create a new cURL resource
        $this->ch = curl_init();

        $this->option = $option;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    { 
        // Set the cURL options
        curl_setopt_array($this->ch, $this->option);

        // Execute the cURL request
        $response = curl_exec($this->ch);

        // Check for cURL errors
        if (curl_errno($this->ch)) {
            $error = curl_error($this->ch);
            // Handle the error accordingly
            throw new \Exception(__("cURL Error: ") . $error);
        }

        // Close the cURL resource
        curl_close($this->ch);
    }
}
