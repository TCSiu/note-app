<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if($validator->fails()){
            return $this->sendError('Login Fail', $validator->errors());
        }
        $validated = $validator->validated();
        if(!$token = Auth::guard('api')->attempt($validated)){
            return $this->unauthorized();
        }
        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ];
        return $this->sendResponse($data, 'Login Success');
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|unique:users,name',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|confirmed',
        ]);
        if($validator->fails()){
            return $this->sendError('Login Fail', $validator->errors());
        }
        $validated = $validator->validated();
        $user_create = User::create($validated);
        $user = User::find($user_create->id);

        $token = Auth::guard('api')->attempt($validated);

        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ];

        return $this->sendResponse($data, 'Login Success');
    }

    public function test(){
        return $this->sendResponse(['test'], 'test');
    }

    public function validateUser(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);
        if($validator->fails()){
            return $this->sendError('Email doesn\'t exist', $validator->errors());
        }
        $validated = $validator->validated();
        $user = User::where(['email' => $validated['email']])->first();
        return $this->sendResponse($user, 'Email exist');
    }

    public function checkToken(Request $request){
        try {
            $isNotExpired = JWTAuth::parseToken()->authenticate();
            return $this->sendResponse(true, "Token is not expired");
        } catch(TokenExpiredException $e) {
            return $this->sendError("Token Already Expired", $e->getMessage(), 401);
        }
    }

    public function getRefreshToken(Request $request){
        try {
            $token = JWTAuth::getToken();
            $refresh_token = JWTAuth::refresh($token);
            return $this->sendResponse($refresh_token, 'Get Refresh Token');
        } catch(TokenBlacklistedException $e) {
            return $this->sendError("Token Already Refreshed", $e->getMessage(), 401);
        } 
        
    }
}
