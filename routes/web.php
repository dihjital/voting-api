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
$router->post('/login/mobile', ['uses' => 'AuthController@loginMobile']);

// $router->post('/register', ['uses' => 'AuthController@register']);

$router->group(['middleware' => 'auth'], function () use ($router) {
  $router->post('/logout', ['uses' => 'AuthController@logout']);
});

$router->group(['middleware' => [
    'auth', 
    'scope:close-question',
    'renew_session',
    'check_session']
  ], 
  function() use ($router) {
    $router->patch('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@openQuestion']);
});

$router->group(['middleware' => [
    'auth', 
    'scope:list-questions,list-votes,vote']
  ], 
  function() use ($router) {
    $router->post('/session', ['uses' => 'SessionController@createSession']);
    $router->delete('/session/{session_id}', ['uses' => 'SessionController@deleteSession']);
});

$router->group(['middleware' => [
    'auth', 
    'scopes:create-question,create-vote', 
    'renew_session', 
    'check_session', 
    'is_closed']
  ], 
  function () use ($router) {
    $router->post('/questions', ['uses' => 'QuestionController@createQuestion']);
    $router->post('/questions/{question_id: [0-9]+}/votes', ['uses' => 'VoteController@createVote']);
});

$router->group(['middleware' => [
    'auth', 
    'scopes:modify-question,modify-vote', 
    'renew_session', 
    'check_session', 
    'is_closed']
  ], 
  function () use ($router) {
    $router->put('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@modifyQuestion']);
    $router->put('/questions/{question_id: [0-9]+}/votes/{vote_id: [0-9]+}', ['uses' => 'VoteController@modifyVote']);
});

$router->group(['middleware' => [
    'auth', 
    'scopes:delete-question,delete-votes,delete-vote', 
    'renew_session', 
    'check_session', 
    'is_closed']
  ], 
  function () use ($router) {
    $router->delete('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@deleteQuestion']);
    $router->delete('/questions/{question_id: [0-9]+}/votes/{vote_id: [0-9]+}', ['uses' => 'VoteController@deleteVote']);
    $router->delete('/questions/{question_id: [0-9]+}/votes', ['uses' => 'VoteController@deleteAllVotesforQuestion']);
});

// If session-id is provided then we will try to get the relevant user id from cache
$router->group(['middleware' => [
    'auth', 
    'scopes:list-questions,list-votes',
    // TODO: merge_user_id, renew_session and check_session should be merged together
    'merge_user_id']
  ], 
  function() use ($router) {
    // Questions
    $router->get('/questions', ['uses' => 'QuestionController@showAllQuestions']);
    $router->get('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@showOneQuestion']);
    // Votes (A question might have multiple votes)
    $router->get('/questions/{question_id: [0-9]+}/votes', ['uses' => 'VoteController@showAllVotesforQuestion']);
    $router->get('/questions/{question_id: [0-9]+}/votes/{vote_id: [0-9]+}', ['uses' => 'VoteController@showOneVote']);
    // Summary
    $router->get('/summary', ['uses' => 'SummaryController@getSummary']);
    // Locations (A vote might have multiple locations)
    $router->get('/questions/{question_id: [0-9]+}/votes/locations', ['uses' => 'LocationController@showAllLocationsforQuestion']);
});

// Allows to place a vote
$router->group(['middleware' => [
    'auth',
    'scopes:vote',
    'renew_session',
    'check_session',
    'is_closed']
  ], 
  function () use ($router) {
    $router->patch('/questions/{question_id: [0-9]+}/votes/{vote_id: [0-9]+}', ['uses' => 'VoteController@increaseVoteNumber']);
});

// Request FCM tokens for push notifications
$router->post('/subscribe', ['uses' => 'TokenController@storeToken']);
$router->delete('/unsubscribe', ['uses' => 'TokenController@deleteToken']);
