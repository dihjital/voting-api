<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait WithPushNotification
{

    protected $ch;
    protected $options = [];

    public function initWithPushNotification($title, $body, $link): self 
    {
        // Create a new cURL resource
        $this->ch = curl_init();

        $fcm_key = env('FCM_AUTHORIZATION_KEY');

        // Set the cURL options
        $url = "https://fcm.googleapis.com/fcm/send";
        $headers = [
            'Authorization: key='.$fcm_key,
            'Content-Type: application/json'
        ];

        $data = [];

        // TODO: Tag the client keys so they are not going to be mixed with other cached items
        foreach ($this->getClientKeys() as $key) {
            $data[] = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => 'firebase-logo.png',
                    'click_action' => $link
                ],
                'to' => $key 
            ];
        }

        $this->options = array_map(function ($data) use ($url, $headers) {
            return [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($data)
            ];
        }, $data);
        
        return $this;

    }

    protected function getClientKeys()
    {
        $storage = Cache::getStore(); // will return instance of FileStore
        $filesystem = $storage->getFilesystem(); // will return instance of Filesystem
        $dir = (Cache::getDirectory());

        foreach ($filesystem->allFiles($dir) as $file1) {
            if (is_dir($file1->getPath())) {
                foreach ($filesystem->allFiles($file1->getPath()) as $file2) {
                    yield unserialize(substr(File::get($file2->getRealpath()), 10));
                }
            }
        }
    }

    public function sendPushNotification(): array
    {
        $response = [];

        // Loop through all subscribed clients
        foreach ($this->options as $option) {
            // Set the cURL options to the resource
            curl_setopt_array($this->ch, $option);

            // Execute the cURL request
            $response[] = curl_exec($this->ch);

            // Check for cURL errors
            if (curl_errno($this->ch)) {
                $error = curl_error($this->ch);
                // Handle the error accordingly
                throw new \Exception(__("cURL Error: ") . $error);
            }
        }

        // Close the cURL resource
        curl_close($this->ch);

        return $response;
    }

}