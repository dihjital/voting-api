<?php

namespace App\Console\Commands;

use Exception;

use App\Models\Question;

use App\Events\QuestionClosed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:close-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close questions automatically based on their closed_at attribute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Question::where('closed_at', '<', now())
                ->where('is_closed', 0)
                ->tap(function ($questions) {
                    $questions->each(function ($question) {
                        Log::info('app:close-questions is closing question: ' . $question->question_text);
                    });
                })
                ->get()
                ->each
                ->update(['is_closed' => 1]);

            Log::info('app:close-questions command run successfully');
        } catch (Exception $e) {
            Log::error('app:close-questions command failed with: ' . $e->getMessage());
        }
    }
}