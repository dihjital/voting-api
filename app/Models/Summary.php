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

    public function getNumberOfAnswersAttribute()
    {
        return Vote::count();
    }

    public function getNumberOfQuestionsAttribute()
    {
        return Question::count();
    }

    public function getHighestVoteAttribute()
    {
        return Vote::select('questions.id', 
			    'questions.question_text', 
			    'votes.vote_text', 
			    DB::raw('MAX(votes.number_of_votes) AS number_of_votes'))
            ->leftJoin('questions', 'votes.question_id', '=', 'questions.id')
            ->get()
	    ->first();        
    }

    public function getHighestQuestionAttribute()
    {
        return Question::all()->sortByDesc(function ($question) {
                return $question->number_of_votes;
            })->first()->only(['id', 'question_text', 'number_of_votes']);
    }

    public function getMostVotedQuestionAttribute()
    {
        return Vote::select('questions.id',
                            'questions.question_text', 
                            DB::raw('SUM(votes.number_of_votes) as total_votes'))
            ->join('questions', 'votes.question_id', '=', 'questions.id')
            ->groupBy('questions.id')
            ->orderByRaw('SUM(votes.number_of_votes) DESC')
            ->first();
    }

    public function getTotalNumberOfVotesAttribute()
    {
	    return Vote::sum('number_of_votes');
    }

}
