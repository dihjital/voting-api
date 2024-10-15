<?php

namespace App\Jobs;

use App\Models\Question;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Exception;

class EmailResultsToVoter extends Job
{
    private $templateId;
    private $apiKey;

    private $serverlessFunctionUrl;

    private $letters;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private Question $question,
        private $voterEmail)
    {
        $this->templateId = 11; // Brevo mail temaplate id
        $this->apiKey = env('BREVO_RESULTS_API_KEY');

        $this->serverlessFunctionUrl = config('api.serverless-functions.quickchart.url');
        $this->letters = range('A', 'Z');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        try {
            $this->sendEmail();
        } catch (Exception $e) {
            Log::error('EmailResultsToVoter: ' . $e->getMessage());
        }
    }

    protected function getChartUrl()
    {
        $labels = $this->question->votes->map(fn($vote, $index) => $this->letters[$index] . ') ')->toArray();
        $data = $this->question->votes->map(fn($vote) => $vote->number_of_votes)->toArray();

        $backgroundColor = $this->question->votes->map(
            fn($vote) => $this->question->correct_vote === $vote->id
                ? 'red'
                : 'lightblue'
        )->toArray();

        try {
            $response = 
                Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ZjQzMjU0NGUtYTQyNC00NzA5LThjNjgtZDMyZTdhN2Y5ZThjOmtoRFBNRnZGbExjU2kzbHE4VmVvOERhenB0aG5sbjlEOG54b0w5TDc0aEZzZlZnYTBnU1pheFNkRzFCcjJnaTc=',
                ])
                ->post($this->serverlessFunctionUrl, [
                    'labels' => $labels,
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ]);
        } catch (Exception $e) {
            Log::error('getChartUrl: ' . $e->getMessage());
        }

        ! $response->successful() && 
            throw new Exception($response->body(), $response->status());

        $response->json('statusCode') >= 400 &&
            throw new Exception($response->json('body'), $response->json('statusCode'));

        return $response->json('body');        
    }

    protected function createParams()
    {
        return [
            'QUESTION_TEXT' => $this->question->question_text,
            'QUESTION_ID' => $this->question->id,
            'NAME' => $this->voterEmail,
            'CHART_URL' => $this->getChartUrl(),
            'VOTES' => $this->question->votes->map(fn($vote, $index) => [
                'letter' => $this->letters[$index],
                'text' => $vote->vote_text,
                'number' => $vote->number_of_votes,
                'correct_vote' => $this->question->correct_vote === $vote->id ? __(' -= This was the correct vote =- ') : '',
            ]),
        ];
    }

    protected function createEmailAddress()
    {
        return [
            'email' => $this->voterEmail,
            'name' => $this->voterEmail,
        ];
    }

    protected function sendEmail()
    {
        try {
            $response = 
                Http::withHeaders([
                    'accept' => 'application/json',
                    'api-key' => $this->apiKey,
                    'content-type' => 'application/json',
                ])
                ->post('https://api.brevo.com/v3/smtp/email', [
                    'to' => [
                        $this->createEmailAddress(),
                    ],
                    'templateId' => $this->templateId,
                    'params' => $this->createParams(),
                ]);
        } catch (Exception $e) {
            Log::error('sendEmail: ' . $e->getMessage());
        }

        ! $response->successful() && 
            throw new Exception($response->body(), $response->status());

        Log::info('Email successfully sent to: '.$this->voterEmail);
        Log::debug('Response was: '.$response->body());
    }
}