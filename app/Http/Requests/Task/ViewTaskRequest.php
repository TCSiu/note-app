<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\BaseRequest;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class ViewTaskRequest extends BaseRequest
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
            //
        ];
    }

    public function handle() {
        $task = Task::where(['uuid' => $this->route('task_uuid')])->with('attachments')->first();
        $user = Auth::guard('api')->user();
        if(isset($task)){
            throw new HttpResponseException($this->sendResponse($task, 'View Task Success'));
        }
        return $this->sendError('View Task Fail', ['Task is deleted']);
    }
}
