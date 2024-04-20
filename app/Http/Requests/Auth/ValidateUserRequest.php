<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ValidateUserRequest extends BaseRequest
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
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function hadnle() {
        $validated = $this->validated();
        $user = User::where(['email' => $validated['email']])->first();
        return $this->sendResponse($user, 'Email exist');
    }
}
