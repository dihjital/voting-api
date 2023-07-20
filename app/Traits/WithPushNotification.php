<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Jobs\PushNotification;

trait WithPushNotification
{

    protected $url;
    protected $data = [];
    protected $fcm_key;

    public function initWithPushNotification($title, $body, $link): self 
    {

        $this->fcm_key = env('FCM_AUTHORIZATION_KEY');
        $this->url = "https://fcm.googleapis.com/fcm/send";

        $this->headers = [
            'Authorization' => 'key=' . $this->fcm_key,
            'Content-Type' => 'application/json'
        ];
                
        // TODO: Tag the client keys so they are not going to be mixed with other cached items
        foreach ($this->getClientKeys() as $key) {
            $this->data[] = [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => 'firebase-logo.png',
                    'click_action' => $link
                ],
                'to' => $key
            ];
        }

        return $this;

    }

    protected function getClientKeys()
    {
        if (Cache::tags('fcm')->has('subscribers')) {
            $subscribers = Cache::tags('fcm')->get('subscribers');
            foreach ($subscribers as $subscriber) {
                yield $subscriber;
            }
        }
    }

    public function sendPushNotification()
    {
        // Loop through all subscribed clients
        foreach ($this->data as $payload) {
            Log::info(__('Sending push notification to: ').$payload['to']);
            dispatch(new PushNotification($this->url, $this->headers, $payload));
        }
    }

}