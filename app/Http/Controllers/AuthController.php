<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    // User Registration
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            $this->logActivity($user, 'register', 'User', $user->id);

            // Return response
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);

    }

    // User Login
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email|exists:users',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();


        // Check if the user exists and if the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Explicitly return a 401 Unauthorized response with a message
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401); // Return 401 Unauthorized with custom error message
        }else{

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            // log user activity
            $this->logActivity($user, 'login', 'User', $user->id);

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
        }

    }

    // User Logout
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current user's token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }

    // logging user activity
    public function logActivity($user, $action, $modelType, $modelId)
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);
    }

    // view activity log
    public function getActivityLogs(Request $request): JsonResponse
    {
        $logs = ActivityLog::where('user_id', $request->user()->id)->get();

        return response()->json([
            'logs' => $logs
        ]);
    }
}
