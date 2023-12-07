<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            return $this->sendError('Unauthorized', 'Unauthorized');
        }
        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => Auth::guard('api')->user(),
        ];
        return $this->sendResponse($data, 'Login Success');
    }
}
