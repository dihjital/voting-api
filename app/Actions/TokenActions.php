<?php

namespace App\Actions;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TokenActions
{
    const CACHE_EXPIRATION_TIME = 60 * 24 * 7; // 1 week

    public function validateUser($input)
    {
        $validator = Validator::make($input, [
            'user' => 'required|uuid',
        ]);
    
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
    }

    public function validateUserAndToken($input)
    {
        $this->validateUser($input);

        $validator = Validator::make($input, [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
    }

    public function checkIfTokenAlreadyExists($subscriber, $token): bool
    {
        // $subscriber contains all tokens for the specific user ...
        return count(array_filter($subscriber, fn($key) => $key === $token));
    }

    public static function refreshCache($subscribers): void
    {
        Cache::tags('fcm')->flush();
        Cache::tags('fcm')->put('subscribers', $subscribers, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));        
    }
}