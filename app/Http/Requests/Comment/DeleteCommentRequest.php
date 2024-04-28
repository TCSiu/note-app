<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\BaseRequest;
use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteCommentRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::guard('api')->user();
        $comment = Comment::where(['uuid' =>  $this->route('comment_uuid')])->first();
        $task = $comment->tasks;
        if(!$user->comments->contains($comment) || !$task->projects->owners->contains($user)){
            return false;
        }
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
        $comment = Comment::where(['uuid' =>  $this->route('comment_uuid')])->first();
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
