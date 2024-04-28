<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegisterRequest extends BaseRequest
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
            'name'      => 'required|string|unique:users,name',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|confirmed',
        ];
    }

    public function handle() {
        $validated = $this->validated();
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
}
