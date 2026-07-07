<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\B2BPackager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class B2BPackagerLoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $packager = B2BPackager::where('phone', $request->phone)->first();

        if (!$packager || !Hash::check($request->password, $packager->password)) {
            return response()->json(['errors' => ['Invalid credentials']], 401);
        }

        $token = $packager->createToken('B2BPackagerAuth')->accessToken;

        return response()->json([
            'token' => $token,
            // 'zone_topic' => $zoneTopic ?? '', // Only if needed
            // 'topic' => $topic ?? '', // Only if needed
            'packager' => [
                'id' => $packager->id,
                'name' => $packager->name,
                'email' => $packager->email,
                'phone' => $packager->phone,
            ]
        ]);
    }
} 