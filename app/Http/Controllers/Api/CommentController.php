<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\DeleteCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportConsoleCommands\Commands\DeleteCommand;

class CommentController extends BaseController
{
    public function create(CreateCommentRequest $request){
        return $request->handle();
    }

    public function update(UpdateCommentRequest $request){
        return $request->handle();
    }

    public function delete(DeleteCommentRequest $request){
        return $request->handle();
    }
}
