<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

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

    public function logout(Request $request)
    {

      try {
        auth()->user()->tokens()->each(function ($token) {
          $token->delete();
        });
        return response()->json(['status' => 'success', 'message' => 'User logged out successfully'], 200);
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

      $clientId = $request->client_id ?? config('service.passport.client_id');
      $clientSecret = $request->client_secret ?? config('service.passport.client_secret');

      try {

        $response = Http::asForm()->post(config('service.passport.login_endpoint'), [
          'client_secret' => $clientSecret,
          'grant_type' => 'password',
          'client_id' => $clientId,
          'username' => $email,
          'password' => $password,
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
