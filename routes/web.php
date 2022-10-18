<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Questions
$router->get('/questions', ['uses' => 'QuestionController@showAllQuestions']);
$router->get('/questions/{question_id}', ['uses' => 'QuestionController@showOneQuestion']);
$router->delete('/questions/{question_id}', ['uses' => 'QuestionController@deleteQuestion']);
$router->put('/questions/{question_id}', ['uses' => 'QuestionController@modifyQuestion']);

$router->post('/questions', ['uses' => 'QuestionController@createQuestion']);

// Votes (A question might have multiple votes)

$router->post('/questions/{question_id}/votes', ['uses' => 'VoteController@createVote']);
$router->get('/questions/{question_id}/votes', ['uses' => 'VoteController@showAllVotesforQuestion']);
$router->get('/questions/{question_id}/votes/{vote_id}', ['uses' => 'VoteController@showOneVote']);
$router->delete('/questions/{question_id}/votes/{vote_id}', ['uses' => 'VoteController@deleteVote']);
$router->delete('/questions/{question_id}/votes', ['uses' => 'VoteController@deleteAllVotesforQuestion']);

$router->put('/questions/{question_id}/votes/{vote_id}', ['uses' => 'VoteController@modifyVote']);
