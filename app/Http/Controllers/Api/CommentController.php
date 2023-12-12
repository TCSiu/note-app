<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
{
    public function create(Request $request){
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:tasks,id',
            'description' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Comment Fail', $validator->errors());
        }
        $validated = $validator->validated();
        try{
            $task = Task::where(['id' => $validated['task_id'], 'is_deleted' => 0])->first();
            if(!isset($task)){
                return $this->sendError('Create Task Fail', ['Task is deleted']);
            }
            $comment = Comment::create($validated);
            $task->comments()->save($comment);
            return $this->sendResponse($task->comments, 'Create Commnet Success');
        }catch(\Exception $e){
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }
}
