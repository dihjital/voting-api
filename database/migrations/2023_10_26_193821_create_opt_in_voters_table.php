<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptInVotersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opt_in_voters', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('question_id')->nullable();
            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->timestamps();

            $table->unique(['email', 'question_id'], 'unique_email_question');
            $table->unique(['email', 'quiz_id'], 'unique_email_quiz');

            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opt_in_voters');
    }
}
