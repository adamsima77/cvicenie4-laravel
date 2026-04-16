<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Throwable;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Note $note)
    {
        $this->authorize('view', $note);
        $attachments = $note->attachments()
            ->latest()
            ->get();

        return response()->json([
            'attachments' => $attachments,
        ], Response::HTTP_OK);
    }

    public function link(Attachment $attachment)
    {
        $this->authorize('view', $attachment->note);
        $expiresAt = now()->addSeconds(30);

        $url = Storage::disk($attachment->disk)->temporaryUrl($attachment->path, $expiresAt);

        return response()->json([
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request, Note $note)
    {

        $this->authorize('create', $note);
        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max('5mb')],
        ]);

        $disk = 'local';
        $created = [];
        $storedPaths = [];

        try {
            DB::beginTransaction();

            foreach ($validated['files'] as $file) {
                $directory = 'attachments/notes/' . $note->id . '/' . now()->format('Y/m');
                $path = $file->store($directory, $disk);

                $storedPaths[] = $path;

                $created[] = $note->attachments()->create([
                    'public_id' => (string) Str::ulid(),
                    'collection' => 'attachment',
                    'visibility' => 'private',
                    'disk' => $disk,
                    'path' => $path,
                    'stored_name' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            foreach ($storedPaths as $path) {
                Storage::disk($disk)->delete($path);
            }

            return response()->json([
                'message' => 'Prílohy sa nepodarilo uložiť.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Prílohy boli nahrané.',
            'attachments' => $created,
        ], Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();
        return response()->json([
            'message' => 'Príloha bola odstránená'
        ], Response::HTTP_OK);
    }
}
