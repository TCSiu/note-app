<?php

namespace App\Http\Requests\Workflow;

use App\Models\Workflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class GetSuggestedWorkflow extends FormRequest
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
        $user = Auth::guard('api')->user();
        $projects = $user->projects;
        $default_workflow = Workflow::find(1);
        $related_workflow = $projects->pluck('workflowTemplate')->unique()->sortBy('id');
        if(!$related_workflow->contains($default_workflow)){
            $related_workflow->prepend($default_workflow);
        }
        $related_workflow = array_values($related_workflow->toArray());
        return $this->sendResponse($related_workflow, 'Get Suggested Workflow Success');
    }
}
