<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('question_text');
            $table->uuid('user_id');
            $table->boolean('is_closed')->default(false);
            $table->unsignedBigInteger('correct_vote')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('correct_vote')->references('id')->on('votes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
