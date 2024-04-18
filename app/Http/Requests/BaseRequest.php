<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use \Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    public function sendResponse($result, $message)
    {
        $response = [
            'success'   => true,
            'data'      => $result,
            'message'   => $message,
        ];
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMsg = [], $code = 400)
    {
        $response = [
            'success'   => false,
            'message'   => $error,
        ];
        if (!empty($errorMsg)) {
            $response['data'] = $errorMsg;
        }
        return response()->json($response, $code);
    }

    public function unauthorized()
    {
        return $this->sendError('Unauthorised!', ['error' => 'Unauthorised!'], 401);
    }

    public function notFound(){
        return $this->sendError('Record Not Found', ['error' => 'Record not found'], 404);
    }

    protected function failedValidation(Validator $validator) {
        $errors = $validator->errors();

        $response = $this->sendError('Validation Fail', $errors->messages(), 422);

        throw new HttpResponseException($response);
    }
}
