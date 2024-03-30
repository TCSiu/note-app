<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends BaseController
{
    public function getSuggestedWorkflow(Request $request){
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
