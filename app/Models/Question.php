<?php

namespace App\Models;

use App\Events\QuestionClosed;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Question
 *
 * @package voting-api
 *
 * @author  Peter Hrobar <peter.hrobar@votes365.org>
 *
 * @OA\Schema(
 *     description="Question model",
 *     title="Question model",
 *     required={"question_text"},
 *     @OA\Xml(
 *         name="Question"
 *     )
 * )
 */

class Question extends Model
{

    use HasFactory;

    /**
     * @OA\Property(
     *     property="id",
     *     format="integer",
     *     description="Auto-incrementing ID of the question",
     *     title="ID",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="question_text",
     *     format="string",
     *     description="Question text",
     *     title="Question text",
     * )
     *
     * @var string
     */

     /**
     * @OA\Property(
     *     property="is_closed",
     *     type="boolean",
     *     default=false,
     *     description="Determines if the Question is closed for modification or voting",
     *     title="Is closed?",
     * )
     *
     * @var boolean
     */

     /**
     * @OA\Property(
     *     property="number_of_votes",
     *     type="integer",
     *     minimum=0,
     *     description="Number of voting options the question has (calculated field)",
     *     title="Number of votes",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="last_vote_at",
     *     format="datetime",
     *     description="Date and time when the last vote was received for the question (calculated field)",
     *     title="Last date and time when a vote was updated",
     *     example="2023-06-18 12:01:01",
     * )
     *
     * @var \DateTime
     */

    /**
     * @OA\Property(
     *     property="belongs_to_quiz",
     *     type="boolean",
     *     default=false,
     *     description="Determines if the Question belongs to a Quiz or not",
     *     title="Is this Question belongs to a Quiz?",
     * )
     *
     * @var boolean
     */

     /**
     * @OA\Property(
     *     property="created_at",
     *     format="datetime",
     *     default="now",
     *     description="Date and time when the question was created",
     *     title="Created at",
     *     example="2023-06-18 12:01:01",
     * )
     *
     * @var \DateTime
     */

     /**
     * @OA\Property(
     *     property="updated_at",
     *     format="datetime",
     *     default="now",
     *     description="Date and time when the question was updated",
     *     title="Updated at",
     *     example="2023-06-18 12:01:01",
     * )
     *
     * @var \DateTime
     */

    /**
     * @OA\Property(
     *     property="previous_id",
     *     format="integer",
     *     description="ID of the previous Question belonging to the same User",
     *     title="Previous ID",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="next_id",
     *     format="integer",
     *     description="ID of the next Question belonging to the same User",
     *     title="Next ID",
     * )
     *
     * @var integer
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_text',
        'is_closed',
        'closed_at',
        'is_secure',
        'show_current_votes',
        'user_id',
        'correct_vote',
    ];  
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'closed_at' => 'datetime',
    ];
    
    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
    ];

    protected $appends = [
        'previous_id',
        'next_id',
        'number_of_votes',
        'number_of_votes_received',
        'last_vote_at',
        'belongs_to_quiz',
    ];

    public static function boot()
    {
        parent::boot();

        static::updated(function ($question) {
            $question->is_closed &&
                event(new QuestionClosed($question));
        });

        static::deleting(function ($question) {
            $question->votes()->delete();
            $question->quizzes()->detach();
        });
    }

    public function hasCorrectVote(): bool
    {
        return (bool) isset($this->correct_vote) && Vote::find($this->correct_vote);
    }

    public function getCorrectVoteModelAttribute(): Vote | null
    {
        return Vote::find($this->correct_vote);
    }

    public function getNumberOfVotesAttribute()
    {
        return $this->votes->count();
    }

    public function getNumberOfVotesReceivedAttribute()
    {
        return $this->votes->sum('number_of_votes');
    }

    public function getBelongsToQuizAttribute()
    {
        return $this->quizzes()->exists();
    }

    public function getLastVoteAtAttribute()
    {
        return $this->votes()
            ->withVote()
            ->max('voted_at');
    }

    public function getPreviousIdAttribute()
    {
        return request()->has('user_id')
                ? static::where('id', '<', $this->id)
                    ->where('user_id', request('user_id'))
                    ->orderBy('id', 'desc')
                    ->value('id') 
                ?? static::where('user_id', request('user_id'))
                    ->orderBy('id', 'desc')
                    ->value('id')
                : null;
    }

    public function getNextIdAttribute()
    {
        return request()->has('user_id')
                ? static::where('id', '>', $this->id)
                    ->where('user_id', request('user_id'))
                    ->orderBy('id', 'asc')
                    ->value('id')
                ?? static::where('user_id', request('user_id'))
                    ->orderBy('id', 'asc')
                    ->value('id')
                : null;
    }

    /**
     * Get the voting options that belong to this Question
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the the Quizzes that might contain this Question
     */
    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'question_quiz')->withTimestamps();
    }

    public function registered_voters(): HasMany
    {
        return $this->hasMany(QuestionVoter::class, 'question_id');
    }
}
