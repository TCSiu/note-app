<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Auth\CheckTokenRequest;
use App\Http\Requests\Auth\GetRefreshTokenRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ValidateUserRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function login(LoginRequest $request){
        return $request->handle();
    }

    public function register(RegisterRequest $request){
        return $request->handle();
    }

    public function test(){
        return $this->sendResponse(['test'], 'test');
    }

    public function validateUser(ValidateUserRequest $request){
        return $request->hadnle();
    }

    public function checkToken(CheckTokenRequest $request){
        return $request->handle();
    }

    public function getRefreshToken(GetRefreshTokenRequest $request){
        return $request->handle();
    }
}
