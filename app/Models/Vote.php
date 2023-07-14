<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Vote
 *
 * @package voting-api
 *
 * @author  Peter Hrobar <peter.hrobar@gmail.com>
 *
 * @OA\Schema(
 *     description="Vote model",
 *     title="Vote model",
 *     required={"vote_text", "number_of_votes"},
 *     @OA\Xml(
 *         name="Vote"
 *     )
 * )
 */

class Vote extends Model
{

    use HasFactory;

    /**
     * @OA\Property(
     *     property="id",
     *     format="integer",
     *     description="Auto-incrementing ID of the vote",
     *     title="ID",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="vote_text",
     *     format="string",
     *     description="Vote text",
     *     title="Vote text",
     * )
     *
     * @var string
     */

     /**
     * @OA\Property(
     *     property="number_of_votes",
     *     format="integer",
     *     default="0",
     *     description="Number of number of votes received",
     *     title="Number of votes received",
     * )
     *
     * @var integer
     */

     /**
     * @OA\Property(
     *     property="question_id",
     *     format="integer",
     *     description="ID of the question where this vote belongs",
     *     title="Question ID",
     * )
     *
     * @var integer
     */

     /**
     * @OA\Property(
     *     property="created_at",
     *     format="datetime",
     *     default="now",
     *     description="Date and time when the vote was created",
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
     *     description="Date and time when the vote was updated",
     *     title="Updated at",
     *     example="2023-06-18 12:01:01",
     * )
     *
     * @var \DateTime
     */

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vote_text', 
        'number_of_votes', 
        'question_id',
    ];    
    
    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the question that this voting option belongs to
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }    

    public function locations()
    {
        return $this->belongsToMany(Location::class)
                    ->withTimestamps();
    }

}