<?php

namespace App\Listeners;

use App\Models\OptInVoter;
use App\Events\QuestionClosed;
use App\Jobs\EmailResultsToVoter;

use Illuminate\Support\Facades\Log;

use Illuminate\Contracts\Queue\ShouldQueue;

class QuestionClosedListener implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  QuestionClosed $event
     * @return void
     */
    public function handle(QuestionClosed $event)
    {
        // TODO: Implement Quizzes as well
        // Get all the registered voters "belonging" to this question ...
        $voters = OptInVoter::whereHas('question', function ($query) use ($event) {
            $query->where('id', $event->question->id);
        })->get();

        $voters->map(function($voter) {
            Log::debug('Voter e-mail address: '.$voter->email);

            // Send voting results summary e-mail to opted-in voter ...
            Log::debug('Question to process: '.$voter->question->question_text);
            dispatch(new EmailResultsToVoter($voter->question, $voter->email));

            // Once we initiated the e-mail we delete the voter from the opt-in model ...
            $voter->delete();
        });
    }
}

