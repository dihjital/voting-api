<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'ip',
        'country_name',
        'city',
        'latitude',
        'longitude',
    ];    
    
    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    protected $appends = [];

    /**
     * Get the voting options that belong to this Question
     */
    public function votes()
    {
        return $this->belongsToMany(Vote::class)
                    ->withTimestamps();
    }    
}
