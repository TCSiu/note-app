<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends BaseController
{
    public function create(Request $request){
        $user = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|exists:tasks,id',
            'description' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Comment Fail', $validator->errors());
        }
        $validated = $validator->validated();
        DB::beginTransaction();
        try{
            $task = Task::where(['id' => $validated['task_id']])->first();
            if(!isset($task)){
                return $this->notFound();
            }
            $comment = Comment::create($validated);
            $task->comments()->save($comment);
            $user->comments()->save($comment);
            $task->refresh();
            DB::commit();
            return $this->sendResponse($task->comments, 'Create Commnet Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Create Task Fail', $e->getMessage());
        }
    }

    public function update(Request $request, int $comment_id = -1){
        $user = Auth::guard('api')->user();
        $comment = Comment::where(['id' => $comment_id])->first();
        $validator = Validator::make($request->all(), [
            'description' => 'string|nullable',
        ]);
        if($validator->fails()){
            return $this->sendError('Create Comment Fail', $validator->errors());
        }
        if(!isset($comment)){
            return $this->notFound();
        }
        if(!$user->comments->contains($comment)){
            return $this->sendError('Update Comment Fail', ['You aren\'t the owner of this comment']);
        }
        $validated = $validator->validated();
        DB::beginTransaction();
        try{
            $comment->update($validated);
            DB::commit();
            return $this->sendResponse($comment, 'Update Comment Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }

    public function delete(Request $request, $comment_id){
        $user = Auth::guard('api')->user();
        $comment = Comment::where(['id' =>  $comment_id])->first();
        $task = $comment->tasks;
        if(!$user->comments->contains($comment) || !$task->projects->owners->contains($user)){
            return $this->sendError('Update Comment Fail', ['You aren\'t the owner of this comment']);
        }
        DB::beginTransaction();
        try{
            $comment->delete();
            $comment->save();
            DB::commit();
            return $this->sendResponse($comment, 'Delete Comment Success');
        }catch(\Exception $e){
            DB::rollBack();
            return $this->sendError('Fail', $e->getMessage());
        }
    }
}
