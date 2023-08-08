<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Schema(
 *     schema="Summary",
 *     title="Summary model",
 *     description="Summary model, holding information about various statistics.",
 *     @OA\Property(property="number_of_answers", type="integer", description="Number of answers."),
 *     @OA\Property(property="number_of_questions", type="integer", description="Number of questions."),
 *     @OA\Property(property="total_number_of_votes", type="integer", description="Total number of votes."),
 *     @OA\Property(
 *         property="highest_vote",
 *         type="object",
 *         description="Answer with the highest number of votes received",
 *         @OA\Property(property="id", type="integer", description="ID of the question."),
 *         @OA\Property(property="question_text", type="string", description="Text of the question."),
 *         @OA\Property(property="vote_text", type="string", description="Text of the vote."),
 *         @OA\Property(property="number_of_votes", type="integer", description="Number of votes received for the answer."),
 *     ),
 *     @OA\Property(
 *         property="highest_question",
 *         type="object",
 *         description="Question with the most possible answers",
 *         @OA\Property(property="id", type="integer", description="ID of the question."),
 *         @OA\Property(property="question_text", type="string", description="Text of the question."),
 *         @OA\Property(property="number_of_votes", type="integer", description="Number of possible answers for the question."),
 *     ),
 *     @OA\Property(
 *         property="most_voted_question",
 *         type="object",
 *         description="Question with the most received votes",
 *         @OA\Property(property="id", type="integer", description="ID of the question."),
 *         @OA\Property(property="question_text", type="string", description="Text of the question."),
 *         @OA\Property(property="total_votes", type="integer", description="Total votes received for the question."),
 *     ),
 * )
 */

class Summary extends Model
{
    use HasFactory;

    protected $table = null;

    protected $appends = [
        'number_of_answers',
        'number_of_questions',
        'total_number_of_votes',
        'highest_vote',
        'highest_question',
        'most_voted_question',
    ];

    protected $hidden = [
        'uuid',
    ];

    public function setUserIdAttribute($value)
    {
        $this->attributes['uuid'] = $value;
    }

    public function getUserIdAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }

    public function getNumberOfAnswersAttribute()
    {
        return Vote::with('question')
            ->whereHas('question', fn($q) =>
                $q->when($this->user_id, fn($sq) =>
                    $sq->where('user_id', $this->user_id)))    
            ->count();
    }

    public function getNumberOfQuestionsAttribute()
    {
        return Question::when($this->user_id, fn($q) =>
                $q->where('user_id', $this->user_id))
            ->count();
    }

    // Return the answer (Vote model) that received the most votes and it's question
    public function getHighestVoteAttribute()
    {
        $vote = Vote::with('question')
            ->whereHas('question', fn($q) =>
                $q->when($this->user_id, fn($sq) =>
                        $sq->where('user_id', $this->user_id)))
            ->orderByDesc('number_of_votes')
            ->limit(1)
            ->get()
            ->first();

	    $keys = ['id', 'question_text', 'vote_text', 'number_of_votes'];

        return $vote 
		? array_combine($keys, 
            [
            		$vote->question->id,
            		$vote->question->question_text,
            		$vote->vote_text,
            		$vote->number_of_votes
			])
		: array_fill_keys($keys, null);
    }

    // Return the question with the most Votes (number of possible answers)
    public function getHighestQuestionAttribute()
    {
        $keys = ['id', 'question_text', 'number_of_votes'];

        $question = $this->user_id
            ? Question::where('user_id', $this->user_id)->get()
            : Question::all();

        return 
            $question->sortByDesc(fn($q) =>
                    $q->number_of_votes)
                ->first()?->only(['id', 'question_text', 'number_of_votes'])
            ?? array_fill_keys($keys, null);
    }

    // Return the question that received the most cumulative votes for all it's answers (Vote model)
    public function getMostVotedQuestionAttribute()
    {
        $keys = ['id', 'question_text', 'total_votes'];

        return 
            Question::withSum('votes as total_votes', 'number_of_votes')
                ->when($this->user_id, fn($q) =>
                    $q->where('user_id', $this->user_id))
                ->orderByDesc('total_votes')
                ->limit(1)
                ->get()
                ->first()?->only(['id', 'question_text', 'total_votes'])
            ?? array_fill_keys($keys, null);
    }

    public function getTotalNumberOfVotesAttribute()
    {
	    return Vote::with('question')
            ->whereHas('question', fn($q) =>
                $q->when($this->user_id, fn($sq) =>
                        $sq->where('user_id', $this->user_id)))
            ->sum('number_of_votes');
    }
}
