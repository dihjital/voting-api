<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizVoter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quiz_id',
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
     * Get the quiz associated with the opt-in voter.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}