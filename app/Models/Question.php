<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

    use HasFactory;

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
    ];    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    protected $appends = ['number_of_votes'];

}
