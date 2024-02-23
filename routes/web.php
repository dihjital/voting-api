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

use Illuminate\Support\Facades\Auth;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

\Dusterio\LumenPassport\LumenPassport::routes($router->app, ['prefix' => 'v1/oauth']);

$router->post('/login', ['uses' => 'AuthController@login']);
$router->post('/login/mobile', ['uses' => 'AuthController@loginMobile']);

$router->post('/register', ['uses' => 'AuthController@register']);

// This is a simple route to check if the provided Bearer token is still valid or not ...
$router->get('/v1/oauth/token/validate', function () {
  return response()->json(['valid' => Auth::guard('api')->check()]);
});

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
    'scopes:create-quiz,create-question,create-vote', 
    'renew_session', 
    'check_session', 
    'is_closed']
  ], 
  function () use ($router) {
    $router->post('/quizzes', ['uses' => 'QuizController@createQuiz']);
    $router->post('/questions', ['uses' => 'QuestionController@createQuestion']);
    $router->post('/questions/{question_id: [0-9]+}/votes', ['uses' => 'VoteController@createVote']);
});

$router->group(['middleware' => [
    'auth', 
    'scopes:modify-quiz,modify-question,modify-vote', 
    'renew_session', 
    'check_session', 
    'is_closed']
  ], 
  function () use ($router) {
    $router->put('/quizzes/{quiz_id: [0-9]+}', ['uses' => 'QuizController@modifyQuiz']);
    $router->put('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@modifyQuestion']);
    $router->put('/questions/{question_id: [0-9]+}/votes/{vote_id: [0-9]+}', ['uses' => 'VoteController@modifyVote']);
});

$router->group(['middleware' => [
    'auth', 
    'scopes:delete-quiz,delete-question,delete-votes,delete-vote', 
    'renew_session', 
    'check_session', 
    'is_closed']
  ], 
  function () use ($router) {
    $router->delete('/quizzes/{quiz_id: [0-9]+}', ['uses' => 'QuizController@deleteQuiz']);
    $router->delete('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@deleteQuestion']);
    $router->delete('/questions/{question_id: [0-9]+}/votes/{vote_id: [0-9]+}', ['uses' => 'VoteController@deleteVote']);
    $router->delete('/questions/{question_id: [0-9]+}/votes', ['uses' => 'VoteController@deleteAllVotesforQuestion']);
});

// If session-id is provided then we will try to get the relevant user id from cache
$router->group(['middleware' => [
    'auth', 
    'scopes:list-quizzes,list-questions,list-votes',
    // TODO: merge_user_id, renew_session and check_session should be merged together
    'renew_session',
    'merge_user_id']
  ], 
  function() use ($router) {
    // Quizzes
    $router->get('/quizzes', ['uses' => 'QuizController@showAllQuizzes']);
    $router->get('/quizzes/{quiz_id: [0-9]+}', ['uses' => 'QuizController@showOneQuiz']);
    $router->get('/quizzes/{quiz_id: [0-9]+}/questions', ['uses' => 'QuizController@getQuestions']);
    // Questions
    $router->get('/questions', ['uses' => 'QuestionController@showAllQuestions']);
    $router->get('/questions/{question_id: [0-9]+}', ['uses' => 'QuestionController@showOneQuestion']);
    $router->get('/questions/{question_id: [0-9]+}/quizzes', ['uses' => 'QuestionController@getQuizzesForQuestion']);
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

// Opt in Voters so they can receive the result of a voting once the question is closed
// Or all questions are closed in the relevant quiz
$router->post('/voters/opt-in', ['uses' => 'VoterController@optInVoter']);