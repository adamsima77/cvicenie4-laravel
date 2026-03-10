<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = DB::table('notes')
            ->whereNull('deleted_at')
            ->orderBy('updated_at', 'desc')
            ->get();
        return response()->json(['notes' => $notes], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::table('notes')->insert([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'body' => $request->body,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Poznámka bola úspešne vytvorená.'
        ], Response::HTTP_CREATED);
    }

    public function statsByStatus()
    {
        $stats = DB::table('notes')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json([
            'stats' => $stats
        ]);
    }

    public function archiveOldDrafts()
    {
        $affected = DB::table('notes')
            ->where('status', 'draft')
            ->where('updated_at', '<', now()->subDays(30))
            ->update([
                'status' => 'archived',
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Staré koncepty boli archivované.',
            'affected_rows' => $affected
        ]);
    }

    public function getNotesByCategory(Request $request){
        $category_id = trim($request->query('category'));

        if (!$category_id || !is_numeric($category_id)) {
            return response()->json([
                'message' => 'Pre nájdenie poznámok musí byť špecifikovaná kategória'
            ], Response::HTTP_BAD_REQUEST);
        }

        $notes = DB::table('notes')
                 ->join('note_category', 'notes.id', '=', 'note_category.note_id')
                 ->where('note_category.category_id', $category_id)
                 ->orderBy('notes.updated_at', 'desc')
                 ->select('notes.id', 'notes.title', 'notes.body', 'notes.status')
                 ->get();

        return response()->json([
            'notes' => $notes
        ], Response::HTTP_OK);
    }

    public function userNotesWithCategories(string $userId)
    {
        $notes = DB::table('notes')
            ->join('note_category', 'notes.id', '=', 'note_category.note_id')
            ->join('categories', 'note_category.category_id', '=', 'categories.id')
            ->where('notes.user_id', $userId)
            ->orderBy('notes.updated_at', 'desc')
            ->select('notes.id', 'notes.title', 'categories.name as category')
            ->get();

        return response()->json([
            'notes' => $notes
        ], Response::HTTP_OK);
    }

    public function getPinnedNotes(){
        $notes = DB::table('notes')
                ->where('is_pinned', '1')
                ->select('notes.title', 'notes.title', 'notes.body')
                ->get();
        return response()->json([
            'notes' => $notes
        ],Response::HTTP_OK);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $notes = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->where(function ($x) use ($q) {
                $x->where('title', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%");
            })
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'query' => $q,
            'notes' => $notes,
        ], Response::HTTP_OK);
    }

    /**y
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'note' => $note
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $note = DB::table('notes')->where('id', $id)->first();

        if (!$note) {
            return response()->json([
                'message' => 'Poznámka nenájdená.'
            ], Response::HTTP_NOT_FOUND);
        }

        DB::table('notes')->where('id', $id)->update([
            'title' => $request->title,
            'body' => $request->body,
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Poznámka bola úspešne aktualizovaná.'
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id) // toto je soft delete
    {
        $note = DB::table('notes')
            ->whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        if (!$note) {
            return response()->json(['message' => 'Poznámka nenájdená.'], Response::HTTP_NOT_FOUND);
        }

        DB::table('notes')->where('id', $id)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);

//        DB::table('notes')->where('id', $id)->delete();

        return response()->json(['message' => 'Poznámka bola úspešne odstránená.'], Response::HTTP_OK);
    }

}
