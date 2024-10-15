<?php

namespace App\Jobs;

use App\Models\Question;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailResultsToVoter extends Job
{
    private $templateId;
    private $apiKey;
    private $chartId;

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
        } catch (\Exception $e) {
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

        $url = "https://faas-fra1-afec6ce7.doserverless.co/api/v1/namespaces/fn-0bc28cb8-f671-491a-a17d-6d724af0f3fc/actions/votes365.org/quickchart?blocking=true&result=true";

        $response = 
            Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ZjQzMjU0NGUtYTQyNC00NzA5LThjNjgtZDMyZTdhN2Y5ZThjOmtoRFBNRnZGbExjU2kzbHE4VmVvOERhenB0aG5sbjlEOG54b0w5TDc0aEZzZlZnYTBnU1pheFNkRzFCcjJnaTc=',
            ])
            ->post($url, [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => $backgroundColor,
            ])
            ->throwUnlessStatus(200);

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
                ])
                ->throwUnlessStatus(200);

        } catch (\Exception $e) {
            Log::error('sendEmail: '.$e->getMessage());
        }

        ! $response->successful() && 
            throw new \Exception($response->body(), $response->status());

        Log::info('Email successfully sent to: '.$this->voterEmail);
        Log::debug('Response was: '.$response->body());
    }
}