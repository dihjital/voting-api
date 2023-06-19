<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{

    use HasFactory;
    /**
     * Get the question that this voting option belongs to
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
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
