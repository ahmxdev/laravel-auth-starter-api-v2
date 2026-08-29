<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => new UserResource($user)
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $authenticated = Auth::attempt($data);

        if (!$authenticated) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth-token');

        return response()->json([
            'message' => 'User logged in successfully.',
            'user' => new UserResource($user),
            'token' => $token->plainTextToken
        ], 200);
    }
    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        $token->delete();

        return response()->json([
            'message' => 'User logged out successfully.',
        ], 200);
    }
    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ], 200);
    }
}
