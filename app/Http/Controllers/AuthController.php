<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return ResponseHelper::jsonResponse(false, 'Invalid credentials', null, 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $data = [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];

            return ResponseHelper::jsonResponse(true, 'Login successful', $data, 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ResponseHelper::jsonResponse(true, 'Logout successful', null, 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();
            return ResponseHelper::jsonResponse(true, 'User Profile', new UserResource($user), 200);
        } catch (\Throwable $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
