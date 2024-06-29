<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QuestionVoter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question_id',
        'email',
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
     * Get the question associated with the opt-in voter.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}