<?php

namespace App\Console\Commands;

use Exception;

use App\Models\Question;

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
    protected $description = 'Close questions automatically base on their closed_at attribute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Question::where('closed_at', '<', now())->update(['is_closed' => 1]);
        } catch (Exception $e) {
            Log::error('app:close-questions command failed with: ' . $e->getMessage());
        }
    }
}