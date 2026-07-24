<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Google_Client;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $clientId = config('services.google.client_id');
        $client = new Google_Client(['client_id' => $clientId]);

        try {
            // 1. Decrypt & Verify Google's ID token cryptographically
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                return response()->json(['error' => 'Invalid or expired Google token'], 401);
            }

            // 2. Safely retrieve user data directly from Google's claims
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $avatar = $payload['picture'] ?? null;

            // 3. MySQL Database Find-Or-Create Lookup
            $user = User::where('email', $email)->first();

            if ($user) {
                // If they signed up traditionally before, link their Google ID
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleId,
                        'avatar_url' => $avatar,
                    ]);
                }
            } else {
                // Brand-new registration
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar_url' => $avatar,
                    'password' => null, // Password stays safe as null
                ]);
            }

            // 4. Generate a secure access token via Laravel Sanctum
            $authToken = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $authToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Google Authentication Failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}