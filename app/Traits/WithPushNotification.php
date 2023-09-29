<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Jobs\PushNotification;

use App\Models\Question;
use App\Models\Vote;

trait WithPushNotification
{
    protected $url;
    protected $data = [];
    protected $fcm_key;

    public function initWithPushNotification(Question $question, Vote $vote, $link, $action): self 
    {
        $this->fcm_key = env('FCM_AUTHORIZATION_KEY');
        $this->url = "https://fcm.googleapis.com/fcm/send";

        $functionName = match($action) {
            'vote' => 'generateTitleAndBodyForVotePushNotification',
            'delete' => 'generateTitleAndBodyForDeletePushNotification',
            default => 'generateTitleAndBodyForVotePushNotification',
        };

        list('title' => $title, 'body' => $body) = $this->$functionName($vote);

        $this->headers = [
            'Authorization' => 'key=' . $this->fcm_key,
            'Content-Type' => 'application/json'
        ];
                
        // TODO: Tag the client keys so they are not going to be mixed with other cached items
        foreach ($this->getClientKeys($question->user_id) as $key) {
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

    protected function generateTitleAndBodyForVotePushNotification(Vote $vote): array
    {
        return [
            'title' => $vote->question->question_text . ' / '. $vote->vote_text,
            'body' => __('Number of votes increased to :vote_number', ['vote_number' => $vote->number_of_votes]),
        ];
    }

    protected function generateTitleAndBodyForDeletePushNotification(Vote $vote): array
    {
        return [
            'title' => __('Vote :vote_text deleted', ['vote_text' => $vote->vote_text]),
            'body' => __('Vote :vote_text deleted for :question_text', ['vote_text' => $vote->vote_text, 'question_text' => $vote->question->question_text]),
        ];
    }

    protected function getClientKeys(string $user_id = '')
    {
        if (Cache::tags('fcm')->has('subscribers')) {
            $subscribers = 
                array_filter(
                    Cache::tags('fcm')->get('subscribers'), 
                    fn($subscriber) => $subscriber === $user_id,
                    ARRAY_FILTER_USE_KEY,
                );

            foreach (array_values($subscribers)[0] ?? [] as $subscriber) {
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