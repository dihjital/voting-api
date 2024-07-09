<?php

namespace App\Events;

use App\Models\Question;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class QuestionClosed
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Question  $question
     * @return void
     */
    public function __construct(
        public Question $question)
    { }
}

