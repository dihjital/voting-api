<?php

namespace Database\Factories;

use App\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use DB;

class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Vote::class;

    public function definition()
    {

      $questionIDs = DB::table('questions')->pluck('id');

      return [
          'vote_text' => $this->faker->text(),
          'number_of_votes' => $this->faker->randomDigit,
          'question_id' => $this->faker->randomElement($questionIDs),
      ];
    }

}
