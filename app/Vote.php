<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    /**
     * Get the question that this voting option belongs to
     */
    public function question()
    {
        return $this->belongsTo('\App\Question');
    }    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vote_text', 'number_of_votes', 'question_id'
    ];    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
