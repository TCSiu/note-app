<?php

namespace App\Http\Requests\Comment;

use App\Http\Requests\BaseRequest;
use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateCommentRequest extends BaseRequest
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
            'description' => 'string|nullable',
        ];
    }

    public function handle() {
        $user = Auth::guard('api')->user();
        $comment = Comment::where(['uuid' => $this->route('comment_uuid')])->first();
        if(!isset($comment)){
            throw new HttpResponseException($this->notFound());
        }
        if(!$user->comments->contains($comment)){
            throw new HttpResponseException($this->sendError('Update Comment Fail', ['error' => 'You aren\'t the owner of this comment']));
        }
        $validated = $this->validated();
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
}
