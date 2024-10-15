<?php

namespace App\Jobs;

use App\Models\Question;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use QuickChart;

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
        $this->chartId = 'zm-41edf3ab-98d8-4c72-a40b-2108634ea421';

        // This is the URL to edit the chart
        // https://quickchart.io/chart-maker/edit/zm-41edf3ab-98d8-4c72-a40b-2108634ea421

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
            Log::error('EmailResultsToVoter: '.$e->getMessage());
        }
    }

    protected function _createChartUrl()
    {
        return 
            'https://quickchart.io/chart/render/' . 
            $this->chartId . 
            '?' .
            http_build_query([
                'labels' => 
                    implode(',', 
                        $this->question->votes->map(
                            fn($vote, $index) => $this->letters[$index] . ') '
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

    protected function createChartUrl()
    {
        $qc = new QuickChart([
            'width' => 300,
            'height' => 200,
        ]);

        $labels = json_encode(
            $this->question->votes->map(
                fn($vote, $index) => $this->letters[$index] . ') '
            )->toArray()
        );

        $data = json_encode(
            $this->question->votes->map(
                function ($vote) {
                    return $vote->number_of_votes;
                },
            )->toArray()
        );

        $config = <<<EOD
        {
            "type": "bar",
            "data": {
                "datasets": [
                {
                    "label": "Dataset 1",
                    "data": $data,
                    "backgroundColor": [
                        "lightblue",
                        "lightblue",
                        "red",
                        "lightblue"
                    ],
                    "fill": false,
                    "spanGaps": false,
                    "lineTension": 0.4,
                    "pointRadius": 3,
                    "pointHoverRadius": 3,
                    "pointStyle": "circle",
                    "borderDash": [
                        0,
                        0
                    ],
                    "barPercentage": 0.9,
                    "categoryPercentage": 0.8,
                    "type": "bar",
                    "hidden": false
                }
                ],
                "labels": $labels
            },
            "options": {
                "title": {
                    "display": false,
                    "position": "top",
                    "fontSize": 12,
                    "fontFamily": "sans-serif",
                    "fontColor": "#666666",
                    "fontStyle": "bold",
                    "padding": 10,
                    "lineHeight": 1.2,
                    "text": "Chart title"
                },
                "layout": {
                    "padding": {},
                    "left": 0,
                    "right": 0,
                    "top": 0,
                    "bottom": 0
                },
                "legend": {
                    "display": false,
                    "position": "top",
                    "align": "center",
                    "fullWidth": true,
                    "reverse": false,
                    "labels": {
                        "fontSize": 12,
                        "fontFamily": "sans-serif",
                        "fontColor": "#666666",
                        "fontStyle": "normal",
                        "padding": 10
                    }
                },
                "scales": {
                "xAxes": [
                    {
                    "display": true,
                    "id": "X1",
                    "position": "bottom",
                    "type": "category",
                    "stacked": false,
                    "offset": true,
                    "distribution": "linear",
                    "gridLines": {
                        "display": true,
                        "color": "rgba(0, 0, 0, 0.1)",
                        "borderDash": [
                            1,
                            1
                        ],
                        "lineWidth": 3,
                        "drawBorder": true,
                        "drawOnChartArea": false,
                        "drawTicks": false,
                        "tickMarkLength": 10,
                        "zeroLineWidth": 1,
                        "zeroLineColor": "rgba(0, 0, 0, 0.25)",
                        "zeroLineBorderDash": [
                            0,
                            0
                        ]
                    },
                    "angleLines": {
                        "display": true,
                        "color": "rgba(0, 0, 0, 0.1)",
                        "borderDash": [
                            0,
                            0
                        ],
                        "lineWidth": 1
                    },
                    "pointLabels": {
                        "display": true,
                        "fontColor": "#666",
                        "fontSize": 10,
                        "fontStyle": "normal"
                    },
                    "ticks": {
                        "display": true,
                        "fontSize": 12,
                        "fontFamily": "sans-serif",
                        "fontColor": "#666666",
                        "fontStyle": "normal",
                        "padding": 0,
                        "stepSize": null,
                        "minRotation": 0,
                        "maxRotation": 50,
                        "mirror": false,
                        "reverse": false
                    },
                    "scaleLabel": {
                        "display": false,
                        "labelString": "Axis label",
                        "lineHeight": 1.2,
                        "fontColor": "#666666",
                        "fontFamily": "sans-serif",
                        "fontSize": 12,
                        "fontStyle": "normal",
                        "padding": 4
                    }
                    }
                ],
                "yAxes": [
                    {
                    "type": "logarithmic",
                    "display": true,
                    "id": "Y1",
                    "position": "left",
                    "stacked": false,
                    "offset": true,
                    "time": {
                        "unit": false,
                        "stepSize": 1,
                        "displayFormats": {
                            "millisecond": "h:mm:ss.SSS a",
                            "second": "h:mm:ss a",
                            "minute": "h:mm a",
                            "hour": "hA",
                            "day": "MMM D",
                            "week": "ll",
                            "month": "MMM YYYY",
                            "quarter": "[Q]Q - YYYY",
                            "year": "YYYY"
                        }
                    },
                    "distribution": "linear",
                    "gridLines": {
                        "display": false,
                        "color": "rgba(0, 0, 0, 0.1)",
                        "borderDash": [
                            0,
                            0
                        ],
                        "lineWidth": 1,
                        "drawBorder": false,
                        "drawOnChartArea": false,
                        "drawTicks": false,
                        "tickMarkLength": 10,
                        "zeroLineWidth": 1,
                        "zeroLineColor": "rgba(0, 0, 0, 0.25)",
                        "zeroLineBorderDash": [
                            0,
                            0
                        ]
                    },
                    "angleLines": {
                        "display": true,
                        "color": "rgba(0, 0, 0, 0.1)",
                        "borderDash": [
                            0,
                            0
                        ],
                        "lineWidth": 1
                    },
                    "pointLabels": {
                        "display": true,
                        "fontColor": "#666",
                        "fontSize": 10,
                        "fontStyle": "normal"
                    },
                    "ticks": {
                        "display": false,
                        "fontSize": 12,
                        "fontFamily": "sans-serif",
                        "fontColor": "#666666",
                        "fontStyle": "normal",
                        "padding": 0,
                        "stepSize": null,
                        "minRotation": 0,
                        "maxRotation": 50,
                        "mirror": false,
                        "reverse": false
                    },
                    "scaleLabel": {
                        "display": false,
                        "labelString": "Axis label",
                        "lineHeight": 1.2,
                        "fontColor": "#666666",
                        "fontFamily": "sans-serif",
                        "fontSize": 12,
                        "fontStyle": "normal",
                        "padding": 4
                    }
                    }
                ]
                },
                "plugins": {
                "roundedBars": "true"
                },
                "grid": {
                "display": false
                },
                "cutoutPercentage": 50,
                "rotation": -1.5707963267948966,
                "circumference": 6.283185307179586,
                "startAngle": -1.5707963267948966
            }
        }
        EOD;

        $qc->setConfig($config);
        return $qc->getUrl();
    }

    protected function createParams()
    {
        return [
            'QUESTION_TEXT' => $this->question->question_text,
            'QUESTION_ID' => $this->question->id,
            'NAME' => $this->voterEmail,
            'CHART_URL' => $this->createChartUrl(),
            'VOTES' => $this->question->votes->map(fn($vote, $index) => [
                'letter' => $this->letters[$index],
                'text' => $vote->vote_text,
                'number' => $vote->number_of_votes,
                'correct_vote' => $this->question->correct_vote === $vote->id ? __('This was the correct vote') : '',
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

        } catch (\Exception $e) {
            Log::error('sendEmail: '.$e->getMessage());
        }

        ! $response->successful() && 
            throw new \Exception($response->body(), $response->status());

        Log::info('Email successfully sent to: '.$this->voterEmail);
        Log::debug('Response was: '.$response->body());
    }
}