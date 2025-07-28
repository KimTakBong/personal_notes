<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NoteController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = auth()->user()->notes()->latest()->get();
        $sharedNotes = auth()->user()->sharedNotes()->with('user')
            ->orderByDesc('note_user.last_shared_at')
            ->get();
        return view('notes.index', compact('notes', 'sharedNotes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notes.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required'
        ]);

        $create = auth()->user()->notes()->create($request->all());
        // dd($request->all(), $create->toArray());

        return redirect()->route('notes.index')->with('success', 'Note created!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        $this->authorize('update', $note);
        
        return view('notes.form', compact('note'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        $this->authorize('update', $note);
    
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required'
        ]);

        $note->update($request->all());

        // Set notif updated ke semua user yang menerima share note ini
        $note->sharedWith()->syncWithoutDetaching(
            $note->sharedWith->pluck('id')->mapWithKeys(function($id) {
                return [$id => ['is_updated' => 1, 'is_read' => 0]];
            })->toArray()
        );

        return redirect()->route('notes.index')->with('success', 'Note updated!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);
        $note->delete();
        return back()->with('success', 'Note deleted!');
    }

    /**
     * Share the specified resource with another user.
     */
    public function share(Request $request, Note $note)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Hindari duplikasi share
        if (!$note->sharedWith->contains($user->id)) {
            $note->sharedWith()->attach($user->id, [
                'is_read' => 0,
                'is_updated' => 0,
                'last_shared_at' => now(),
            ]);
        } else {
            // Jika sudah pernah di-share, update last_shared_at dan reset notif
            $note->sharedWith()->updateExistingPivot($user->id, [
                'is_read' => 0,
                'is_updated' => 0,
                'last_shared_at' => now(),
            ]);
        }

        return back()->with('success', 'Note shared!');
    }

    /**
     * Get the users with whom the note is shared.
     */
    public function sharedUsers(Note $note)
    {
        // Ambil user yang sudah di-share, hanya email dan id
        $users = $note->sharedWith()->select('users.id', 'users.email')->get();

        return response()->json($users);
    }

    /**
     * Remove the share of the specified resource from a user.
     */
    public function unshare(Note $note, User $user)
    {
        $note->sharedWith()->detach($user->id);

        // Jika request AJAX, return JSON
        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        // Jika bukan AJAX, redirect
        return redirect()->route('notes.index')->with('success', 'Note unshare!');
    }

    /**
     * Mark the specified resource as read.
     */
    public function markAsRead(Note $note)
    {
        $user = auth()->user();
        $user->sharedNotes()->updateExistingPivot($note->id, [
            'is_read' => 1,
            'is_updated' => 0
        ]);
        return response()->json(['success' => true]);
    }

    /**
     * Get the comments for the specified note.
     */
    public function comments(Note $note)
    {
        $comments = $note->comments()->with('user')->orderBy('created_at')->get();
        return response()->json($comments);
    }

    /**
     * Add a comment to the specified note.
     */
    public function addComment(Request $request, Note $note)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        $user = auth()->user();
        $isOwner = $note->user_id === $user->id;
        $comment = $note->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
            'is_read_owner' => $isOwner ? true : false,
            'is_read_shared' => $isOwner ? false : true,
        ]);
        $comment->load('user');
        return response()->json($comment);
    }

    /**
     * Mark the comments of the specified note as read.
     */
    public function markCommentsAsRead(Note $note)
    {
        $user = auth()->user();
        $isOwner = $note->user_id === $user->id;
        if ($isOwner) {
            $note->comments()->where('is_read_owner', false)->update(['is_read_owner' => true]);
        } else {
            $note->comments()->where('is_read_shared', false)->where('user_id', '!=', $user->id)->update(['is_read_shared' => true]);
        }
        return response()->json(['success' => true]);
    }
}
