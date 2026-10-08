<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.'
            ], 401);
        }

        $token = $user->createToken('auth-token');

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email not verified.',
                'user' => new UserResource($user),
                'token' => $token->plainTextToken
            ], 403);
        }

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
    public function verifyEmail(VerifyEmailRequest $request)
    {
        if (! $request->fulfill()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 409);
        }

        return response()->json([
            'message' => 'Email verified successfully.'
        ], 200);
    }
    public function sendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.'
            ], 409);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent successfully.'
        ], 200);
    }
}
