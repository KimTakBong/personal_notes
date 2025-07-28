<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::group(['prefix' => 'notes'], function () {
        Route::get('/', [NoteController::class, 'index'])->name('notes.index');
        Route::get('/create', [NoteController::class, 'create'])->name('notes.create');
        Route::post('/', [NoteController::class, 'store'])->name('notes.store');
        Route::get('/{note}/edit', [NoteController::class, 'edit'])->name('notes.edit');
        Route::put('/{note}', [NoteController::class, 'update'])->name('notes.update');
        Route::delete('/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
        Route::get('/{note}', [NoteController::class, 'show'])->name('notes.show');
        Route::post('/{note}/share', [NoteController::class, 'share'])->name('notes.share');
        Route::get('/{note}/shared-users', [NoteController::class, 'sharedUsers'])->name('notes.sharedUsers');
        Route::delete('/{note}/unshare/{user}', [NoteController::class, 'unshare'])->name('notes.unshare');
        Route::post('/{note}/mark-as-read', [NoteController::class, 'markAsRead'])->name('notes.markAsRead');
        Route::get('/{note}/comments', [NoteController::class, 'comments'])->name('notes.comments');
        Route::post('/{note}/comments', [NoteController::class, 'addComment'])->name('notes.addComment');
        Route::post('/{note}/comments/mark-read', [NoteController::class, 'markCommentsAsRead'])->name('notes.markCommentsAsRead');
    });

    Route::get('/users/email-suggestions', function(Request $request) {
        return \App\Models\User::where('email', 'like', '%' . $request->q . '%')->limit(10)->pluck('email');
    });
});

require __DIR__.'/auth.php';
