<?php

namespace App\Actions;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StoreToken extends TokenActions
{
    /**
     * Store a new token.
     *
     * @param  array<string, string>  $input
     */
    public function store(array $input): bool
    {
        $this->validateUserAndToken($input);

        $key = $input['user'];

        $subscribers = Cache::tags('fcm')->get('subscribers', []);

        // If the key doesn't exist or the token doesn't exist for that key
        if (!isset($subscribers[$key]) || !$this->checkIfTokenAlreadyExists($subscribers[$key], $input['token'])) {
            $subscribers[$key][] = $input['token'];
            
            // Decide on cache refreshing strategy based on if it's the first item
            // TODO: This can be simplified to self::refreshCache() only
            if (empty($subscribers)) {
                Cache::tags('fcm')->put('subscribers', $subscribers, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));
            } else {
                self::refreshCache($subscribers);
            }
            
            Log::info('Token: ' . $input['token'] . ' registered successfully for: ' . $key);
            return true;
        }

        Log::error('Token: '.$input['token'].' already registered for: '.$key);
        return false;
    }
}