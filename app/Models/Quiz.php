<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Quiz
 *
 * @package voting-api
 *
 * @author  Peter Hrobar <peter.hrobar@gmail.com>
 *
 * @OA\Schema(
 *     description="Location model holding Geo information about the voters",
 *     title="Location model",
 *     required={"ip", "country_name", "city", "latitude", "longitude"},
 *     @OA\Xml(
 *         name="Location"
 *     )
 * )
 */

class Quiz extends Model
{
    use HasFactory;

    /**
     * @OA\Property(
     *     property="id",
     *     format="integer",
     *     description="Auto-incrementing ID of the location",
     *     title="ID",
     * )
     *
     * @var integer
     */

    /** 
     * @OA\Property(
     *     property="ip", 
     *     type="string",
     *     format="ipv4",
     *     description="The IP address of the location",
     *     title="IP address (IPV4)",
     *     example="8.8.8.8"
     * )
     * 
     * @var ipv4
     */

    /**
     * @OA\Property(
     *     property="country_name", 
     *     type="string", 
     *     description="The name of the country",
     *     title="Country name",
     *     example="Hungary"
     * )
     * 
     * @var string
     */

    /**
     * @OA\Property(
     *     property="city", 
     *     type="string", 
     *     description="The name of the city",
     *     title="City name",
     *     example="Budapest"
     * )
     * 
     * @var string
     */

    /**
     * @OA\Property(
     *     property="latitude", 
     *     type="number", 
     *     format="float",
     *     description="The latitude coordinates",
     *     title="Latitude"
     * )
     * 
     * @var float
     */

    /**
     * @OA\Property(
     *     property="longitude", 
     *     type="number", 
     *     format="float", 
     *     description="The longitude coordinates",
     *     title="Longitude"
     * )
     * 
     * @var float
     */

    /**
     * @OA\Property(
     *     property="created_at",
     *     format="datetime",
     *     default="now",
     *     description="Date and time when the location was created",
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
     *     description="Date and time when the location was updated",
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
    ];    
    
    /**
     * The attributes that are excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    protected $appends = [];

    /**
     * Get the questions that might belong to this Quiz
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_quiz');
    }    
}
