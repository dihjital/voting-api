<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Question;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\ServiceProvider;

use Laravel\Passport\Passport;
use Dusterio\LumenPassport\LumenPassport;

use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        LumenPassport::routes($this->app);
        LumenPassport::tokensExpireIn(Carbon::now()->addDays(5));

        // TODO: For the mobile users token we should have a longer exiry date
        // LumenPassport::tokensExpireIn(Carbon::now()->addDays(5), env('PASSPORT_CLIENT_ID')); 

        // This is required as well in case our grant-type is token_refresh
	    Passport::tokensExpireIn(now()->addDays(5));

        Passport::tokensCan([
            'list-quizzes' => 'List quizzes',
            'list-questions' => 'List questions',
            'list-votes' => 'List votes belonging to questions',
            'create-quiz' => 'Create a new quiz',
            'create-question' => 'Create a new question',
            'create-vote' => 'Create a new vote for a question',
            'delete-quiz' => 'Delete a single quiz',
            'delete-question' => 'Delete a single question',
            'delete-votes' => 'Delete all votes belonging to a question',
            'delete-vote' => 'Delete a single vote belonging to a question',
            'modify-quiz' => 'Modify a single quiz',
            'modify-question' => 'Modify a single question',
            'modify-vote' => 'Modify a single vote',
            'close-question' => 'Open/close question for voting',
            'vote' => 'Submit a vote for a question',
        ]);

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });

        Gate::define('create-new-vote', function (User $user, Question $question) {
            return $question->number_of_votes < config('api.defaults.vote.max_number_of_votes');
        });

        Gate::define('create-new-question', function (User $user, string $Uuid) {
            return Question::where('user_id', $Uuid)
                ->where('is_closed', 0)
                ->count() < config('api.defaults.question.max_number_of_questions');
        });

        Gate::define('open-question', function (User $user, string $Uuid) {
            return Question::where('user_id', $Uuid)
                ->where('is_closed', 0)
                ->count() + 1 <= config('api.defaults.question.max_number_of_questions');
        });
    }
}
