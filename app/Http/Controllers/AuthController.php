<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Cache};
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\{SignUpRequest, Login};

class AuthController extends Controller
{
    //Methods for authentication functionality
    public function register(SignUpRequest $request): JsonResponse
    {
        // Logic for handling user registration
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], Response::HTTP_CREATED);
    }

    public function login(Login $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->generateUserRole();

        Cache::put('user' . $user->id, $user, now()->addHour(1));

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token
        ], Response::HTTP_OK);
    
        }
        public function logout(): JsonResponse
    {

        Cache::forget('user' . auth()->user()->id);
        // Logic for handling user logout
        auth()->logout();

        return response()->json([
            'message' => 'User logged out successfully'
        ], Response::HTTP_OK);
    }
    }

