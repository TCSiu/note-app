<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class GetRefreshTokenRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

    public function handle() {
        try {
            $token = JWTAuth::getToken();
            $refresh_token = JWTAuth::refresh($token);
            return $this->sendResponse($refresh_token, 'Get Refresh Token');
        } catch(TokenBlacklistedException $e) {
            return $this->sendError("Token Already Refreshed", $e->getMessage(), 401);
        } 
    }
}
