<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class QptInVoter
 *
 * @package voting-api
 *
 * @author  Peter Hrobar <peter.hrobar@gmail.com>
 *
 * @OA\Schema(
 *     description="Model for holding the e-mail address of those voters who would like to receive the result of a vote once the relevant Question is closed.",
 *     title="Opt-In-Voter model",
 *     required={"email", "question_id"}
 * )
 */

class OptInVoter extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="id",
     *     format="integer",
     *     description="Auto-incrementing ID of the Opt-In-Voter model",
     *     title="ID",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="email", 
     *     type="string", 
     *     format="email",
     *     description="E-mail address of the voter",
     *     title="E-mail address",
     *     example="abc@def.com"
     * )
     * 
     * @var string
     */

    /**
     * @OA\Property(
     *     property="question_id", 
     *     type="integer", 
     *     format="number",
     *     description="ID of the relevant Question model",
     *     title="Question ID",
     *     example="12"
     * )
     * 
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="quiz_id", 
     *     type="integer", 
     *     format="number",
     *     description="ID of the relevant Quiz model. There is an either or relation between the Question ID and the Quiz ID.",
     *     title="Quiz ID",
     *     example="1"
     * )
     * 
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="created_at",
     *     format="datetime",
     *     default="now",
     *     description="Date and time when the voter e-mail address was registered",
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
     *     description="Date and time when the voter registration was altered",
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
        'email',
        'question_id',
        'quiz_id',
    ];    
    
    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes which are added to the model's JSON form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Set the email attribute and validate it.
     *
     * @param  string  $value
     * @return void
     */
    public function setEmailAttribute($value): void
    {
        // Validate the email
        $validator = Validator::make(['email' => $value], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->attributes['email'] = $value;
    }

    /**
     * Get the question associated with the opt-in voter.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }    

    /**
     * Get the quiz associated with the opt-in voter.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}