<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $this->authorize('viewAny', Note::class);

        $notes = Note::query()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with([
                'user:id,first_name,last_name',
                'categories:id,name,color',
                'attachments:id,attachable_id,attachable_type,file_name'
            ])
            ->whereIn('status', ['published', 'archived'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(5);

        return response()->json([
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    public function myNotes(Request $request)
    {
        $this->authorize('viewAny', Note::class);

        $notes = $request->user()
            ->notes()
            ->select(['id', 'user_id', 'title', 'body', 'status', 'is_pinned', 'created_at'])
            ->with([
                'categories:id,name,color',
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(5);

        return response()->json([
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    public function pinNote(string $id){
        $note = Note::find($id);
        if(!$note){
            return response()->json(['message' => 'Poznámka neexistuje.'], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('pin', $note);
         $note->pin();
         return response()->json(['message' => 'Poznámka bola úspešne pripnutá.'], Response::HTTP_OK);
    }

    public function unpinNote(string $id){
        $note = Note::find($id);
        if(!$note){
            return response()->json(['message' => 'Poznámka neexistuje.'], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('unpin', $note);
        $note->unpin();
        return response()->json(['message' => 'Poznámka bola úspešne odopnutá.'], Response::HTTP_OK);
    }

    public function archiveNote(string $id){
        $note = Note::find($id);
        if(!$note){
            return response()->json(['message' => 'Poznámka neexistuje.'], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('archive', $note);
        $note->archive();
        return response()->json(['message' => 'Poznámka bola archivovaná.'], Response::HTTP_OK);
    }

    public function publishNote(string $id){
        $note = Note::find($id);
        if(!$note){
            return response()->json(['message' => 'Poznámka neexistuje'], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('publish', $note);
        $note->publish();
        return response()->json(['message' => 'Poznámka bola uverejnená.'], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $this->authorize('create', Note::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],

            'categories' => ['sometimes', 'array', 'max:3'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        // alebo lepšie riešenie, len potom odstráňte z fillable user_id...
        $note = $request->user()->notes()->create([
            'title'     => $validated['title'],
            'body'      => $validated['body'] ?? null,
            'status'    => $validated['status'] ?? 'draft',
            'is_pinned' => $validated['is_pinned'] ?? false,
        ]);

        if (!empty($validated['categories'])) {
            $note->categories()->sync($validated['categories']);
        }

        return response()->json([
            'message' => 'Poznámka bola úspešne vytvorená.',
            'note' => $note->load([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ]),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        $note = Note::with([
            'tasks:id,note_id,title,is_done,due_at',
            'comments:id,body,commentable_id,commentable_type',
            'user:id,first_name,last_name,email',
            'categories:id,name,color'
        ])->find($id);


        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        $this->authorize('view', $note);

        return response()->json(['note' => $note], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(
                ['message' => 'Poznámka nenájdená.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->authorize('update', [Note::class, $note]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'is_pinned' => ['sometimes', 'boolean'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);

        $note->update($validated);

        if (array_key_exists('categories', $validated)) {
            $note->categories()->sync($validated['categories']);
        }

        return response()->json([
            'message' => 'Poznámka bola aktualizovaná.',
            'note' => $note->load([
                'user:id,first_name,last_name',
                'categories:id,name,color',
            ]),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }
        $this->authorize('delete', $note);
        $note->delete();
        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }

    public function statsByStatus()
    {
        $stats = Note::whereNull('deleted_at')
                       ->selectRaw('COUNT(*) as count')
                       ->groupBy('status')
                       ->orderBy('status')
                       ->get();

        $this->authorize('view', [Note::class, $stats]);

        return response()->json(['stats' => $stats], Response::HTTP_OK);
    }

    public function archiveOldDrafts()
    {
        $affected = Note::whereNull('deleted_at')
                          ->where('status', 'draft')
                          ->where('updated_at', '<', now()->subDays(30))
                          ->update([
                              'status' => 'archived',
                          ]);
        $this->authorize('archive_old', [Note::class, $affected]);
        return response()->json([
            'message' => 'Staré koncepty boli archivované.',
            'affected_rows' => $affected,
        ]);
    }

    public function userNotesWithCategories(string $userId)
    {
        $rows = Note::join('note_category', 'notes.id', '=', 'note_category.note_id')
                     ->join('categories', 'note_category.category_id', '=', 'categories.id')
                     ->where('notes.user_id', $userId)
                     ->whereNull('notes.deleted_at')
                     ->orderBy('notes.updated_at', 'desc')
                     ->select('notes.id', 'notes.title', 'categories.name as category')
                     ->get();
        $this->authorize('user_notes', [Note::class, $rows]);
        return response()->json(['notes' => $rows], Response::HTTP_OK);
    }
}
