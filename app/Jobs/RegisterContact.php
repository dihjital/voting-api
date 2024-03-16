<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

use Brevo\Client\Configuration;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Model\CreateContact;

use GuzzleHttp;

class RegisterContact extends Job
{
    private $listIds;
    private $apiKey;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private $voterEmail
    )
    {
        $this->listIds = [5]; // Brevo list ids
        $this->apiKey = env('BREVO_CONTACT_API_KEY');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {        
        try {
            $apiInstance = $this->getBrevoApiInstance();
            $apiInstance->createContact($this->createContact());
        } catch (\Exception $e) {
            Log::error('RegisterContact: '.$e->getMessage());
        }
    }

    protected function getBrevoApiInstance(): ?ContactsApi
    {
        Log::debug('api-key: ' . $this->apiKey);
        $conf = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $this->apiKey);

        return new ContactsApi(
            new GuzzleHttp\Client(),
            $conf
        );
    }

    protected function createContact()
    {
        $contact = new CreateContact();

        $contact['email'] = $this->voterEmail;
        $contact['listIds'] = $this->listIds;
        $contact['updateEnabled'] = true;

        return $contact;
    }
}