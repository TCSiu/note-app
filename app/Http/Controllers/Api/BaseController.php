<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller as Controller;

class BaseController extends Controller
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

    public function sendError($error, $errorMsg = [], $code = 404)
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
        return $this->sendError('Unauthorised!', ['error' => 'Unauthorised!'], 403);
    }

    public function notFound(){
        return $this->sendError('Record Not Found', ['error' => 'Record not found'], 404);
    }
}
