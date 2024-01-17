<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageController extends BaseController
{
    public function upload(Request $request){
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
        ]);
        if($validator->fails()){
            return $this->sendError('Fail to upload image', $validator->errors());
        }
        $image_file = $request->file('image')->store('image', 'public');
        return $this->sendResponse($image_file, 'Upload Success');
    }
}
