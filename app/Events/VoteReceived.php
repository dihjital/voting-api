<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Question;

use Illuminate\Support\Facades\Log;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class VoteReceived extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public Question $question,
    )
    { 
        Log::info('Sending info to Pusher channel about a new vote for: ' . $this->question->question_text);
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [
            'user.'.$this->question->user_id,
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'user_id' => $this->question->user_id,
            'question' => $this->question->id,
        ];
    }
}