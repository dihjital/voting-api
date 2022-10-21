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

      $name = $request->name;
      $email = $request->email;
      $password = $request->password;

      if (empty($name) || empty($email) || empty($password)) {
        return response()->json(['status' => 'error', 'message' => 'Required field is missing'], 400);
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return response()->json(['status' => 'error', 'message' => 'You must enter a valid e-mail address'], 400);
      }

      if (User::where('email', '=', $email)->exists()) {
        return response()->json(['status' => 'error', 'message' => 'User already exists with the give e-mail address'], 400);
      }

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

      $email = $request->email;
      $password = $request->password;

      if (empty($email) || empty($password))
      {
        return response()->json(['status' => 'error', 'message' => 'You must fill in all required fields'], 400);
      }

      try {

        $response = Http::asForm()->post(config('service.passport.login_endpoint'), [
          'client_secret' => config('service.passport.client_secret'),
          'grant_type' => 'password',
          'client_id' => config('service.passport.client_id'),
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
