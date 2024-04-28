<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FileController extends BaseController
{
    public function upload(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'usage' => 'nullable|string',
            'usage_uuid' => 'required_with:usage|uuid',
        ]);
        if($validator->fails()) {
            return $this->sendError('Fail to upload image', $validator->errors());
        }
        $upload_file = $request->file('file');
        $filename = $upload_file->getClientOriginalName();
        $file_size = $upload_file->getSize();
        dd($file_size);
        $filepath = $request->file('file')->store('image', 'public');
        
        $file = File::create(['filename' => $filename, 'size' => $file_size, 'path' => $filepath, ]);
        

        return $this->sendResponse($filepath, 'Upload Success');
    }
}
