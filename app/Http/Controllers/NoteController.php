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
     * Display the specified note detail page.
     */
    public function show($id)
    {
        $note = Note::with(['comments.user', 'sharedWith'])->findOrFail($id);
        $sharedUsers = $note->sharedWith;
        $comments = $note->comments()->with('user')->orderBy('created_at')->get();
        return view('notes.show', compact('note', 'sharedUsers', 'comments'));
    }

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
            'content' => 'required',
            'is_public' => 'nullable|boolean',
        ]);
        $data = $request->all();
        $data['is_public'] = $request->has('is_public');
        $create = auth()->user()->notes()->create($data);
        return redirect()->route('notes.index')->with('success', 'Note created!');
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
        $validateRules = [
            'is_public' => 'nullable|boolean',
        ];
        if ($request->has('title')) {
            $validateRules['title'] = 'required|max:255';
        }
        if ($request->has('content')) {
            $validateRules['content'] = 'required';
        }
        $validated = $request->validate($validateRules);
        $validated['is_public'] = $request->has('is_public');
        $update = $note->update($validated);
        // Set notif updated ke semua user yang menerima share note ini
        $note->sharedWith()->syncWithoutDetaching(
            $note->sharedWith->pluck('id')->mapWithKeys(function($id) {
                return [$id => ['is_updated' => 1, 'is_read' => 0]];
            })->toArray()
        );
        return redirect()->route('notes.show', $note)->with('success', 'Note updated!');
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

        // Cek referer, jika dari detail note, redirect ke detail
        $referer = request()->headers->get('referer');
        $detailUrl = route('notes.show', $note, false);
        if ($referer && str_contains($referer, $detailUrl)) {
            return redirect()->route('notes.show', $note)->with('success', 'Note unshare!');
        }
        // Default: redirect ke index
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
        $user = $request->user();
        // Access control for comments
        if ($note->is_public) {
            if (!$user) {
                abort(403, 'Login required to comment on public notes.');
            }
        } else {
            // Private note: only owner or shared users can comment
            $isOwner = $note->user_id === $user->id;
            $isShared = $note->sharedUsers()->where('user_id', $user->id)->exists();
            if (!$isOwner && !$isShared) {
                abort(403, 'You do not have permission to comment on this note.');
            }
        }
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        $note->comments()->create([
            'user_id' => $user->id,
            'content' => $request->content,
        ]);
        return back();
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

    /**
     * Public view for a note (no login required if public)
     */
    public function publicShow($id)
    {
        $note = Note::with(['comments.user', 'sharedWith', 'user'])->findOrFail($id);
        if (!$note->is_public) {
            abort(403, 'This note is private.');
        }
        $sharedUsers = $note->sharedWith;
        $comments = $note->comments()->with('user')->orderBy('created_at')->get();
        return view('notes.public_show', compact('note', 'sharedUsers', 'comments'));
    }
}
