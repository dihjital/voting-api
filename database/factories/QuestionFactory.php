<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'is_closed' => false,
            'is_secure' => false,
            'user_id' => 'b0447212-73d8-40ab-9610-055cba4be62c',
        ];
    }
}
