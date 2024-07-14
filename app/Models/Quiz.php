<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Quiz
 *
 * @package voting-api
 *
 * @author  Peter Hrobar <peter.hrobar@votes365.org>
 *
 * @OA\Schema(
 *     description="Quiz model, containing questions which belong to this Quiz",
 *     title="Quiz model",
 *     required={"name", "user_id"}
 * )
 */

class Quiz extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="id",
     *     format="integer",
     *     description="Auto-incrementing ID of the Quiz",
     *     title="Quiz ID",
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="name", 
     *     type="string", 
     *     description="The name of the Quiz",
     *     title="Quiz name",
     *     example="A quiz related to the meaning of life?"
     * )
     * 
     * @var string
     */

    /**
     * @OA\Property(
     *     property="user_id",
     *     type="string",
     *     format="uuid",
     *     description="The UUID of the user who owns this Quiz",
     *     title="User ID"
     * )
     * 
     * @var uuid
     */

    /**
     * @OA\Property(
     *     property="number_of_questions",
     *     format="integer",
     *     description="Number of Questions belonging to this Quiz",
     *     title="Number of questions"
     * )
     *
     * @var integer
     */

    /**
     * @OA\Property(
     *     property="created_at",
     *     format="datetime",
     *     default="now",
     *     description="Date and time when the Quiz was created",
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
     *     description="Date and time when the Quiz was updated",
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
        'name',
        'user_id',
        'is_secure',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_secure' => 'boolean',
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
        'number_of_questions',
        'has_secure_question',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($quiz) {
            $quiz->questions()->detach();
        });
    }

    public function getNumberOfQuestionsAttribute()
    {
        return $this->questions()->where('is_closed', 0)->count();
    }

    public function getHasSecureQuestionAttribute(): bool
    {
        return $this->questions()->where('is_secure', 1)->exists();
    }

    /**
     * Get the questions that might belong to this Quiz
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_quiz')->withTimestamps();
    }
    
    public function registered_voters(): HasMany
    {
        return $this->hasMany(QuizVoter::class, 'quiz_id');
    }
}