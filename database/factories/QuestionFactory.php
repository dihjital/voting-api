<?php

namespace Database\Factories;

use App\Question;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */

    protected $model = Question::class;

    public function definition()
    {
        return [
            'question_text' => $this->faker->text(),
        ];
    }
}
