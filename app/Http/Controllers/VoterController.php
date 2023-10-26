<?php

namespace App\Http\Controllers;

use App\Models\Quiz;

use Illuminate\Http\Request;

use App\Actions\RegisterNewOptIn;

class VoterController extends Controller
{
    public function optInVoter(Request $request, RegisterNewOptIn $registerNewOptIn)
    {
        try {
            $registerNewOptIn->register($request->all());
        } catch (\Exception $e) {
            return response()->json(self::eWrap(__($e->getMessage())), $e->getCode());
        }

        return response()->json(self::sWrap(__('Opted-in successfully')), 201);
    }
}
