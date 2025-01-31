<?php

namespace App\Jobs;

use App\Models\Vote;
use App\Models\Question;

use App\Events\VoteReceived;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

use Exception;
use Carbon\Carbon;

class ProcessVote implements ShouldQueue
{
    protected int $questionId;
    protected int $voteId;
    protected string $uuid;
    protected string $actionType;
    protected ?string $voterIPAddress;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($questionId, $voteId, $uuid, $actionType, $voterIPAddress)
    {
        $this->questionId = $questionId;
        $this->voteId = $voteId;
        $this->uuid = $uuid;
        $this->actionType = $actionType;
        $this->voterIPAddress = $voterIPAddress;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $question = Question::findOrFail($this->questionId);
        $vote = Vote::findOrFail($this->voteId);

        $this->validateJob($question, $vote);
        $this->performAction($question, $vote);
        
        Log::debug('Vote received for question: ' . $question->question_text);
    }

    protected function performAction(Question $q, Vote $v): void
    {
        match ($this->actionType) {
            'vote' => $this->increaseVoteNumber($q, $v),
            default => $this->increaseVoteNumber($q, $v),
        };
    }

    protected function increaseVoteNumber(Question $q, Vote $v)
    {
        $v->update([
            'number_of_votes' => $v->number_of_votes + 1,
            'voted_at' => Carbon::now(),
        ]);

        // Pusher notification
        event(new VoteReceived($q));
    }

    protected function validateJob(Question $q, Vote $v): void
    {
        if ($q->is_closed) throw new Exception("The question '{$q->question_text}' is closed");
        if ($v->question->id !== $q->id) throw new Exception("Vote '{$v->vote_text}' does not belong to the given question");
        if ($q->user_id !== $this->uuid) throw new Exception("The given user does not have a '{$q->question_text}' question");
    }

    public function failed(Exception $e)
    {
        Log::error('ProcessVote job failed with: ' . $e->getMessage());
    }
}
