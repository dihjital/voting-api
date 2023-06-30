<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Question
 *
 * @package voting-api
 *
 * @author  Peter Hrobar <peter.hrobar@gmail.com>
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
     *     property="number_of_votes",
     *     format="integer",
     *     description="Number of voting options the question has (calculated field)",
     *     title="Number of votes",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="last_updated_at_vote",
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

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($question) {
            $question->votes()->delete();
        });
    }

    public function getNumberOfVotesAttribute()
    {
        return Vote::where('question_id', '=', $this->id)->count();
    }

    public function getLastUpdatedAtVoteAttribute()
    {
        return Vote::where('question_id', $this->id)->max('updated_at');
    }

    /**
     * Get the voting options that belong to this Question
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_text'
    ];    
    
    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $appends = [
        'number_of_votes',
        'last_updated_at_vote'
    ];

}
