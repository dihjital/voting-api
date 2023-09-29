<?php

namespace App\Actions;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DeleteToken extends TokenActions
{
    /**
     * Delete a token.
     *
     * @param  array<string, string>  $input
     */
    public function delete(array $input): bool
    {
        $this->validateUser($input);

        $key = $input['user'];

        if (Cache::tags('fcm')->has('subscribers')) {
            $subscribers = Cache::tags('fcm')->get('subscribers');

            if (array_key_exists($key, $subscribers)) {
                foreach ($subscribers[$key] as $token) {
                    Log::info('Token: '.$token.' deleted successfully for: '.$key);
                }

                unset($subscribers[$key]);
                self::refreshCache($subscribers);
                
                return true;
            }
        }

        return false;
    }
}