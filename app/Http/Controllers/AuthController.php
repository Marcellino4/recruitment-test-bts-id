<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            $user = DB::transaction(function () use ($validated) {
                return User::create([
                    'name' => $validated['username'],
                    'username' => $validated['username'],
                    'password' => Hash::make($validated['password']),
                ]);
            });

            return response()->json([
                'status' => 201,
                'message' => 'Registration successful',
            ], 201);
        } catch (Throwable $e) {
            Log::error('Register failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Registration failed. Please try again.'], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 401, 'message' => 'Invalid credentials'], 401);
        }

        try {
            [$authToken, $refreshToken] = DB::transaction(function () use ($user) {
                $user->tokens()->where('name', 'auth_token')->delete();
                $authToken = $user->createToken('auth_token')->plainTextToken;

                $user->refreshTokens()->delete();
                $refreshToken = Str::random(64);
                RefreshToken::create([
                    'user_id' => $user->id,
                    'token' => $refreshToken,
                    'expires_at' => now()->addDays(30),
                ]);

                return [$authToken, $refreshToken];
            });

            return response()->json([
                'status' => 200,
                'authentication_token' => $authToken,
                'refresh_token' => $refreshToken,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Login failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Login failed. Please try again.'], 500);
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshToken = RefreshToken::where('token', $request->refresh_token)->first();

        if (! $refreshToken || $refreshToken->isExpired()) {
            return response()->json(['status' => 401, 'message' => 'Invalid or expired refresh token'], 401);
        }

        try {
            [$authToken, $newRefreshToken] = DB::transaction(function () use ($refreshToken) {
                $user = $refreshToken->user;

                $user->tokens()->where('name', 'auth_token')->delete();
                $authToken = $user->createToken('auth_token')->plainTextToken;

                $refreshToken->delete();
                $newRefreshToken = Str::random(64);
                RefreshToken::create([
                    'user_id' => $user->id,
                    'token' => $newRefreshToken,
                    'expires_at' => now()->addDays(30),
                ]);

                return [$authToken, $newRefreshToken];
            });

            return response()->json([
                'status' => 200,
                'authentication_token' => $authToken,
                'refresh_token' => $newRefreshToken,
            ]);
        } catch (Throwable $e) {
            Log::error('Token refresh failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Token refresh failed. Please try again.'], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $request->user()->currentAccessToken()->delete();
                $request->user()->refreshTokens()->delete();
            });

            return response()->json(['status' => 200, 'message' => 'Logged out successfully']);
        } catch (Throwable $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);

            return response()->json(['status' => 500, 'message' => 'Logout failed. Please try again.'], 500);
        }
    }
}
