<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Question;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

use Laravel\Passport\Passport;
use Dusterio\LumenPassport\LumenPassport;

use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    const MAX_NUMBER_OF_VOTES = 5;
    const MAX_NUMBER_OF_QUESTIONS = 5;
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
        // LumenPassport::tokensExpireIn(Carbon::now()->addDays(5), env('PASSPORT_CLIENT_ID')); 

        // This is required as well in case our grant-type is token_refresh
	    Passport::tokensExpireIn(now()->addDays(5));

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });

        Gate::define('create-new-vote', function (User $user, Question $question) {
            return $question->number_of_votes < self::getMaximumNumberOfVotes();
        });

        Gate::define('create-new-question', function (User $user, string $Uuid) {
            return Question::where('user_id', $Uuid)
                ->where('is_closed', 0)
                ->count() < self::getMaximumNumberOfQuestions();
        });

        Gate::define('open-question', function (User $user, string $Uuid) {
            return Question::where('user_id', $Uuid)
                ->where('is_closed', 0)
                ->count() + 1 <= self::getMaximumNumberOfQuestions();
        });
    }

    protected static function getMaximumNumberOfVotes(): int
    {
        return env('MAX_NUMBER_OF_VOTES', self::MAX_NUMBER_OF_VOTES);
    }

    protected static function getMaximumNumberOfQuestions(): int
    {
        return env('MAX_NUMBER_OF_QUESTIONS', self::MAX_NUMBER_OF_QUESTIONS);
    }
}
