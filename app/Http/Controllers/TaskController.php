<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Note $note)
    {
        // kto môže vidieť note, môže vidieť aj jej tasky
        $this->authorize('view', [Task::class, $note]);

        $tasks = $note->tasks()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'tasks' => $tasks,
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Note $note)
    {
        $this->authorize('create', [Task::class, $note]);
        $validated = $request->validate([
          'title' => ['required', 'string', 'max:255'],
          'is_done' => ['sometimes', 'boolean'],
          'due_at' => ['nullable', 'date'],
        ]);

        $task = $note->tasks()->create($validated);

        return response()->json([
            'message' => 'Úloha bola úspešne vytvorená.',
            'task' => $task,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note, Task $task)
    {
        if($task->note_id != $note->id){
            return response()->json(['message' => 'Úloha nepatrí tejto poznámke.'], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('view', [Task::class, $task]);
        return response()->json(['tasks' => $task, 'note' => $note], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note, Task $task)
    {
        $this->authorize('update', [Task::class, $task]);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_done' => ['sometimes', 'boolean'],
            'due_at' => ['nullable', 'date'],
        ]);

        $task->update($validated);

        return response()->json([
            'message' => 'Úloha bola úspešne aktualizovaná.',
            'task' => $task,
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note, Task $task)
    {
        $this->authorize('delete', [Task::class, $task]);
        if ($task->note_id !== $note->id) {
            return response()->json([
                'message' => 'Úloha nepatrí tejto poznámke.'
            ], Response::HTTP_NOT_FOUND);
        }
        $task->delete();
        return response()->json(['messsage' => 'Úloha bola úspešne vymazaná.'], Response::HTTP_OK);
    }
}
