<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
          $this->authorize('viewAny', Comment::class);
          $comments = Comment::with('user', 'commentable')->latest()->get();
          return response()->json($comments, Response::HTTP_OK);
    }

    public function taskIndex(Task $task){
        $this->authorize('task_index', Comment::class);
        $comments = $task->comments()->latest()->get();
        return response()->json($comments, Response::HTTP_OK);
    }

    public function noteIndex(Note $note){
        $this->authorize('viewAny', Comment::class);
        $comments = $note->comments()->latest()->get();
        return response()->json($comments, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function taskStore(Request $request, Task $task)
    {
        $this->authorize('create_task', [Comment::class, $task]);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $task->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id
        ]);

        return response()->json([
            'message' => 'Comment created'
        ], Response::HTTP_CREATED);
    }

    public function noteStore(Request $request, Note $note){
        $this->authorize('create_note', [Comment::class, $note]);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);
        $note->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id
        ]);
        return response()->json(['message' => 'Comment created'], Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comment = Comment::with('user', 'commentable')->findOrFail($id);
        $this->authorize('view', [Comment::class, $comment]);
        return response()->json($comment, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);
        $comment->update([
            'body' => $validated['body'],
            'user_id' => $request->user()->id
        ]);
        return response()->json(['message' => 'Comment updated'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();
        return response()->json(['message' => 'Comment deleted'], Response::HTTP_OK);
    }
}
