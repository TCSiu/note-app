<?php

namespace App\Observers;

use App\Commons\SystemLogFunction;
use App\Models\Comment;

class CommentObserver
{
    public function saved(Comment $comment){
        if($comment->wasRecentlyCreated){
            SystemLogFunction::storeLog($comment, Comment::class, 'CREATED');
        }else{
            if (!$comment->getChanges()) {
                return;
            }
            if($comment->wasChanged('deleted_at') ||$comment->wasChanged('deleted_by')){
                return;
            }
            SystemLogFunction::storeLog($comment, Comment::class, 'UPDATED');
        }
    }

    public function deleted(Comment $comment){
        SystemLogFunction::storeLog($comment, Comment::class, 'DELETED');
    }

    public function restored(Comment $comment){
        SystemLogFunction::storeLog($comment, Comment::class, 'RESTORE');
    }
}
