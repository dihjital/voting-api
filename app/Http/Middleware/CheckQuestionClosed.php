<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Question;

class CheckQuestionClosed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // TODO: Should be able to use Question like this $request->question if the
        // Controller receives it's parameter type hinted ...
        $question = self::getQuestion($request->route('question_id'));

        // If the question is not present, continue with the request
        if (!$question instanceof Question) {
            return $next($request);
        }

        // We only allow to GET a Question model if it is closed
        if ($request->method() !== 'GET' && $question->is_closed) {
            return response()->json(['status' => 'error', 'message' => __('Question is closed for modification')], 403);
        }

        return $next($request);
    }

    protected static function getQuestion($question_id)
    {
        try {
            return Question::findOrFail($question_id);
        } catch (\Exception $e) {
            return null;
        }
    }
}
