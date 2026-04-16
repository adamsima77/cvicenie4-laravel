<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Note $note): bool
    {
        // všetci prihlásení vidia published alebo archived
        if ($note->status === 'published' || $note->status === 'archived') {
            return true;
        }

        // draft vidí iba vlastník
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // každý prihlásený môže vytvoriť poznámku
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Note $note): bool
    {
        // aktualizovať môže iba vlastník
        return $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    public function archive_old(User $user, Note $note): bool{
        return $note->user_id === $user->id && $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Note $note): bool
    {
        return false;
    }

    public function user_notes(User $user, Note $note): bool{
        return $note->user_id === $user->id;
    }

    public function pin(User $user, Note $note): bool
    {
        return $user->is_admin || $note->user_id === $user->id;
    }

    public function unpin(User $user, Note $note): bool
    {
        return $user->is_admin || $note->user_id === $user->id;
    }

    public function archive(User $user, Note $note): bool
    {
        return $user->is_admin || $note->user_id === $user->id;
    }

    public function publish(User $user, Note $note): bool
    {
        return $user->is_admin || $note->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Note $note): bool
    {
        return false;
    }
}
