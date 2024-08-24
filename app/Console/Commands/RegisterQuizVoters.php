<?php

namespace App\Console\Commands;

use Exception;

use App\Models\QuizVoter;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Brevo\Client\Configuration;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Model\CreateContact;

use GuzzleHttp;

class RegisterQuizVoters extends Command
{
    private $listId = 9; // Brevo list id for quiz-voters
    private $apiKey;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:register-quiz-voters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register quiz voters on Brevo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->apiKey = env('BREVO_CONTACT_API_KEY');

        // TODO: Figure out how to get the last time we run
        $modifiedSince = new \DateTime("2021-10-20T19:20:30+01:00");
        $limit = 50;
        $offset = 0;

        try {
            $apiInstance = $this->getBrevoApiInstance();
            $brevoContactsCount = true;

            $newContacts = QuizVoter::all()->unique('email');

            while ($brevoContactsCount) {
                $result = $apiInstance->getContactsFromList(
                    $this->listId,
                    $modifiedSince,
                    $limit,
                    $offset
                );

                $brevoContacts = collect(json_decode($result));
                $brevoContacts = collect($brevoContacts->get('contacts'));

                $brevoContactsCount = $brevoContacts->count();
                // Log::debug('Number of existing contacts retrieved: ' . $brevoContactsCount);

                $newContacts = 
                    $newContacts->whereNotIn('email', $brevoContacts->pluck('email')->all());
                
                $offset += $limit;
            }
            
            $newContacts->each(
                fn($contact) => $apiInstance->createContact(
                    $this->createContact($contact->email, [$this->listId])
                )
            );
        } catch (Exception $e) {
            Log::error('RegisterQuizVoters: ' . $e->getMessage());
        }
    }

    protected function getBrevoApiInstance(): ?ContactsApi
    {
        $conf = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $this->apiKey);

        return new ContactsApi(
            new GuzzleHttp\Client(),
            $conf
        );
    }

    protected function createContact($email, $listId)
    {
        $contact = new CreateContact();

        $contact['email'] = $email;
        $contact['listIds'] = $listId ?? [$this->listId];
        $contact['updateEnabled'] = true;

        return $contact;
    }
}