<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'c_password' => 'required|same:password'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $success['token'] = $user->createToken('myapp')->plainTextToken;
        $success['name'] = $user->name;

        $response = ['success' => true, 'data' => $success, 'message' => 'User registered successfully'];

        return response()->json($response, 200);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['success' => false, 'message' => 'Invalid login details'], 401);
        }

        $user = Auth::user();
        $success['token'] = $user->createToken('myapp')->plainTextToken;
        $success['name'] = $user->name;

        $response = ['success' => true, 'data' => $success, 'message' => 'User logged in successfully'];

        return response()->json($response, 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout successful']);
    }
}
