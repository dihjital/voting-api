<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

use App\Actions\LoginMobile;

class AuthController extends Controller
{

    public function register(Request $request)
    {

      $validator = validator()->make(request()->all(), [
        'name' => 'required|string',
        'email' => 'email|required|unique:users',
        'password' => [
          'required',
          'min:6',
          'regex:/[a-z]/',      // must contain at least one lowercase letter
          'regex:/[A-Z]/',      // must contain at least one uppercase letter
          'regex:/[0-9]/',      // must contain at least one digit
          'regex:/[@$!%*#?&]/',
        ],
        'password_confirm' => 'required|same:password',
      ]);

      if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
      }

      $name = $request->name;
      $email = $request->email;
      $password = $request->password;

      try {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);
        if ($user->save()) {
          return response()->json(['status' => 'success', 'message' => 'User successfully created'], 200);
        }
      } catch (\Exception $e) {
          return response()->json(['status' => 'error', 'message' => 'Failed to create user'], 500);
      }

    }

    public function loginMobile(Request $request)
    {
      try {
        $login = new LoginMobile($request);
        return response()->json($login->login(), 200);
      } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode());
      }
    }

    public function logout(Request $request)
    {

      try {
        auth()->user()->tokens()->each(function ($token) {
          $token->delete();
        });
        return response()->json(['status' => 'success', 'message' => __('User logged out successfully')], 200);
      } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
      }

    }

    public function login(Request $request)
    {

      $validator = validator()->make(request()->all(), [
        'email' => 'email|required',
        'password' => 'required',
      ]);

      if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
      }

      $email = $request->email;
      $password = $request->password;

      // Get the client id and client secret from the request and use a fall back option in case these are not provided
      $clientId = $request->client_id ?? config('service.passport.client_id');
      $clientSecret = $request->client_secret ?? config('service.passport.client_secret');
      
      // Default scope should allow for listing questions and votes and to vote
      // TODO: '*' should not be allowed as part of the request
      $scope = $request->scope ?? 'list-quizzes list-questions list-votes vote';

      try {

        $response = Http::asForm()->post(config('service.passport.login_endpoint'), [
          'grant_type' => 'password',
          'username' => $email,
          'password' => $password,
          'client_id' => $clientId,
          'client_secret' => $clientSecret,
	        'scope' => $scope,
        ]);

        if ($response->clientError())
          return response()->json(['status' => 'error', 'message' => 'Client error occured'], $response->status());

        if ($response->serverError())
          return response()->json(['status' => 'error', 'message' => 'Server error occured'], $response->status());

      } catch (RequestException $e) {
        return response()->json($e->getMessage(), 500);
      }

      return $response->json();

    }

}
