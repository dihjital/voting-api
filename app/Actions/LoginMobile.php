<?php

namespace App\Actions;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

use Carbon\Carbon;

class LoginMobile
{
    protected $api_user;
    protected $api_secret;

    protected $api_endpoint;

    protected $client_id;
    protected $client_secret;

    public function __construct(protected Request $request)
    {
        $this->api_user = $request->email;
        $this->api_secret = $request->password;

        $this->api_endpoint = config('service.passport.login_endpoint');

        $this->client_id = config('service.passport.mobile.client_id');
        $this->client_secret = config('service.passport.mobile.client_secret');
    }

    protected function checkHalfTime($issued_at, $expires_in): bool
    {
        if (empty($issued_at) || empty($expires_in))
            return true;

        $expires_at = $issued_at->copy()->addSeconds($expires_in);
        $half_time = $issued_at->copy()->average($expires_at);

        return Carbon::now() > $half_time;
    }

    protected function getTokensFromCache(): array
    {
        if (Cache::has('mobile:access_token') && 
            Cache::has('mobile:refresh_token') &&
            Cache::has('mobile:issued_at') &&
            Cache::has('mobile:expires_in')) {
                return [
                    'access_token' => Cache::get('mobile:access_token'),
                    'refresh_token' => Cache::get('mobile:refresh_token'),
                    'issued_at' => Cache::get('mobile:issued_at'),
                    'expires_in' => Cache::get('mobile:expires_in'),
                ];
        }

        return [];
    }

    protected function storeTokensInCache($access_token, $refresh_token, $expires_in): void
    {
        $this->deleteTokensFromCache();

        Cache::put('mobile:access_token', $access_token);
        Cache::put('mobile:refresh_token', $refresh_token);
        Cache::put('mobile:issued_at', Carbon::now());
        Cache::put('mobile:expires_in', $expires_in);
    }

    protected function deleteTokensFromCache(): void
    {
        Cache::forget('mobile:access_token');
        Cache::forget('mobile:refresh_token');
        Cache::forget('mobile:issued_at');
        Cache::forget('mobile:expires_in');
    }

    protected function refreshToken($refresh_token): array
    {
        $response = Http::asForm()->post($this->api_endpoint, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'scope' => 'list-quizzes list-questions list-votes vote',
        ]);

        if ($response->ok()) {
            return [
                'access_token' => $response['access_token'], 
                'refresh_token' => $response['refresh_token'], 
                'expires_in' => $response['expires_in'],
            ];
        }

        $this->deleteTokensFromCache();

        throw new \Exception(json_decode($response->body()), $response->status());
    }

    protected function validateUserLogin()
    {
        $validator = Validator::make($this->request->all(), [
            'email' => 'email|required',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        try {
            $user = User::where('email', $this->request->email)->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(__('Invalid credentials'), 401);
        }
        
        if (!Hash::check($this->request->password, $user->password)) {
            throw new \Exception(__('Invalid credentials'), 401);
        }
    }

    public function login(): array 
    {
        $this->validateUserLogin();
        
        $tokens = $this->getTokensFromCache();

        return $this->areTokensValid($tokens)
            ? $this->handleTokenRefreshIfNeeded($tokens)
            : $this->getNewTokensFromApi();
    }

    protected function areTokensValid(array $tokens): bool
    {
        // Currently we only check the number of items returned and if any of them is null.
        // More complex valiation can be addedd here if required.
        return count($tokens) === 4 && !in_array(null, $tokens, true);
    }

    protected function handleTokenRefreshIfNeeded(array $tokens): array
    {
        list(
            'access_token' => $access_token, 
            'refresh_token' => $refresh_token, 
            'issued_at' => $issued_at, 
            'expires_in' => $expires_in) = $tokens;

        if ($this->checkHalfTime($issued_at, $expires_in)) {            
            list(
                'access_token' => $access_token, 
                'refresh_token' => $refresh_token, 
                'expires_in' => $expires_in) = $this->refreshToken($refresh_token);
            $this->storeTokensInCache($access_token, $refresh_token, $expires_in);
        }

        return $this->extractTokenData([
            'access_token' => $access_token, 
            'refresh_token' => $refresh_token
        ]);
    }

    protected function getNewTokensFromApi(): array
    {
        $response = Http::asForm()->post($this->api_endpoint, [
            'client_secret' => $this->client_secret,
            'grant_type' => 'password',
            'client_id' => $this->client_id,
            'username' => $this->api_user,
            'password' => $this->api_secret,
            'scope' => 'list-quizzes list-questions list-votes vote',
        ]);

        if (!$response->ok()) {
            throw new \Exception(json_decode($response->body()), $response->status());
        }

        $tokens = [
            'access_token' => $response['access_token'], 
            'refresh_token' => $response['refresh_token'],
            'expires_in' => $response['expires_in']
        ];

        $this->storeTokensInCache(...$tokens);

        return $this->extractTokenData($tokens);
    }

    protected function extractTokenData(array $tokens): array
    {
        return [
            'access_token' => $tokens['access_token'], 
            'refresh_token' => $tokens['refresh_token']
        ];
    }
}