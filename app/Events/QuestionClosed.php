<?php

namespace App\Events;

use App\Models\Question;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionClosed
{
    use Dispatchable, SerializesModels;

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

