<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdToQuizzesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table
                ->uuid('user_id')
                ->default('b0447212-73d8-40ab-9610-055cba4be62c') // Dummy user_id for migration
                ->after('name'); // Add the 'user_id' field after 'name'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('user_id'); // Rollback: drop the 'user_id' column
        });
    }
}