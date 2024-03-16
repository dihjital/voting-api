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
        $this->chartId = 'zm-710fe8ea-3310-45af-807e-e10634eb78b7';
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
            Log::error('EmailResultsToVoter: '.$e->getMessage());
        }
    }

    protected function createChartUrl()
    {
        return 
            'https://quickchart.io/chart/render/' . 
            $this->chartId . 
            '?' .
            http_build_query([
                'title' => $this->question->question_text,
                'labels' => 
                    implode(',', 
                        $this->question->votes->map(
                            function ($vote) {
                                return $vote->vote_text;
                            },
                        )->toArray()
                    ),
                'data1' => 
                    implode(',', 
                        $this->question->votes->map(
                            function ($vote) {
                                return $vote->number_of_votes;
                            },
                        )->toArray()
                    ),
            ]);
    }

    protected function createParams()
    {
        return [
            'QUESTION_TEXT' => $this->question->question_text,
            'QUESTION_ID' => $this->question->id,
            'NAME' => $this->voterEmail,
            'CHART_URL' => $this->createChartUrl(),
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

        } catch (\Exception $e) {
            Log::error('sendEmail: '.$e->getMessage());
        }

        ! $response->successful() && 
            throw new \Exception($response->body(), $response->status());

        Log::info('Email successfully sent to: '.$this->voterEmail);
        Log::debug('Response was: '.$response->body());
    }
}