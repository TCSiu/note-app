<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\BaseRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateCommentRequest extends BaseRequest
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
            'task_id' => 'required|integer|exists:tasks,id',
            'description' => 'required|string',
        ];
    }

    public function handle() {
        $user = Auth::guard('api')->user();
        $validated = $this->validated();
        $task = Task::where(['id' => $validated['task_id']])->first();
        if(!isset($task)){
            throw new HttpResponseException($this->notFound());
        }
        DB::beginTransaction();
        try{
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
}
