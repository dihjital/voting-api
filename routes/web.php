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

\Dusterio\LumenPassport\LumenPassport::routes($router->app, ['prefix' => 'v1/oauth']);

$router->post('/login', ['uses' => 'AuthController@login']);
$router->post('/register', ['uses' => 'AuthController@register']);

$router->group(['middleware' => 'auth'], function () use ($router) {
  $router->post('/logout', ['uses' => 'AuthController@logout']);
  $router->delete('/questions/{question_id}', ['uses' => 'QuestionController@deleteQuestion']);
  $router->put('/questions/{question_id}', ['uses' => 'QuestionController@modifyQuestion']);
  $router->post('/questions', ['uses' => 'QuestionController@createQuestion']);
  $router->post('/questions/{question_id}/votes', ['uses' => 'VoteController@createVote']);
  $router->delete('/questions/{question_id}/votes/{vote_id}', ['uses' => 'VoteController@deleteVote']);
  $router->delete('/questions/{question_id}/votes', ['uses' => 'VoteController@deleteAllVotesforQuestion']);
});

// Questions
$router->get('/questions', ['uses' => 'QuestionController@showAllQuestions']);
$router->get('/questions/{question_id}', ['uses' => 'QuestionController@showOneQuestion']);

// Votes (A question might have multiple votes)
$router->get('/questions/{question_id}/votes', ['uses' => 'VoteController@showAllVotesforQuestion']);
$router->get('/questions/{question_id}/votes/{vote_id}', ['uses' => 'VoteController@showOneVote']);
$router->put('/questions/{question_id}/votes/{vote_id}', ['uses' => 'VoteController@modifyVote']);
