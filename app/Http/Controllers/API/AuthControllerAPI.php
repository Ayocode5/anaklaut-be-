<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthUserRegister;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthControllerAPI extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login','register','logout']);
    }

    public function register(AuthUserRegister $request) {
        
        $user = User::create([
            "name" => $request->input('name'),
            "email" => $request->input('email'),
            "password" => Hash::make($request->input('password'))
        ]);

        if($user) {
            return response()->json(["message" => "New User Registered"], 200);
            error_log(sprintf($this->colorFormat['yellow'], "New user" . $request->input('email') . " registered succesfully"));
        }

        return response()->json(["message" => "Failed create new User"], 500);
        
    }

    public function login(Request $request) {

        error_log(sprintf($this->colorFormat['yellow'], "Login attempt from " . $request->input('email')));

        $credentials = $request->only(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    // GET USER INFO
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
