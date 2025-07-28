@extends('layouts.app')

@section('content')
<nav class="bg-gray-100 rounded-lg shadow mb-6 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
        <a href="{{ route('notes.index') }}" class="tab-btn bg-blue-500 text-white px-4 py-2 rounded font-semibold">My Notes</a>
        <span class="text-gray-400">/</span>
        <span class="tab-btn bg-gray-200 text-gray-700 px-4 py-2 rounded font-semibold">Detail</span>
    </div>
    <div class="flex items-center space-x-4">
        <button id="openProfileModal" class="bg-blue-500 text-white px-4 py-2 rounded">Profile</button>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
        </form>
    </div>
</nav>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Kiri: Full Notes -->
    <div class="flex-1 bg-white rounded-lg shadow p-6 flex flex-col">
        <div class="flex items-start justify-between mb-2">
            <h1 class="text-2xl font-bold break-words">{{ $note->title }}</h1>
            <div class="flex items-center gap-2">
                <form action="{{ route('notes.update', $note) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    @method('PUT')
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" onchange="this.form.submit()" {{ $note->is_public ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-sm">Public</span>
                    </label>
                </form>
                @if($note->is_public)
                    <button id="shareUrlBtn" type="button" class="ml-2 flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow transition font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656m-3.656-3.656a4 4 0 015.656 0m-7.07 7.07a8 8 0 1111.314-11.314 8 8 0 01-11.314 11.314z" /></svg>
                        Share URL
                    </button>
                    <div id="shareUrlNotif" class="hidden absolute z-50 top-2 right-2 bg-green-600 text-white px-4 py-2 rounded shadow text-sm animate-fade-in-out">URL copied!</div>
                @endif
                @if(auth()->id() === $note->user_id)
                    <button id="openNoteEditModal" type="button" class="ml-2 flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow transition font-semibold text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13h3l8-8a2.828 2.828 0 10-4-4l-8 8v3z" /></svg>
                        Edit
                    </button>
                @endif
            </div>
        </div>
        <div class="mb-2 text-gray-500 text-xs">Created: {{ $note->created_at->format('d-m-Y H:i') }} | Last updated: {{ $note->updated_at->diffForHumans() }}</div>
        <hr class="mb-4">
        <div class="prose max-w-none mb-4 break-words">{!! $note->content !!}</div>
        <div class="mt-auto">
            <div class="mb-2">
                <span class="font-semibold">Owner:</span> {{ $note->user->name ?? $note->user->email ?? '-' }}
            </div>
            <div>
                <span class="font-semibold">Shared to:</span>
                @if($sharedUsers->count())
                    @foreach($sharedUsers as $user)
                        <span class="bg-blue-200 text-blue-800 rounded-full px-3 py-1 text-xs mr-1 mb-1 inline-block">{{ $user->email }}</span>
                    @endforeach
                @else
                    <span class="text-gray-400">Not shared to anyone</span>
                @endif
            </div>
            @if(auth()->id() === $note->user_id)
                @include('notes.form', ['note' => $note])
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var openEditBtn = document.getElementById('openNoteEditModal');
                    var noteFormModal = document.getElementById('noteFormModal');
                    var closeNoteFormBtn = document.getElementById('closeNoteFormModal');
                    var noteFormTitle = document.getElementById('noteFormModalTitle');
                    var noteForm = noteFormModal ? noteFormModal.querySelector('form') : null;
                    if(openEditBtn && noteFormModal) {
                        openEditBtn.addEventListener('click', function() {
                            noteFormModal.classList.remove('hidden');
                            if(noteFormTitle) noteFormTitle.textContent = 'Edit Note';
                            if(noteForm) {
                                noteForm.action = "{{ route('notes.update', $note) }}";
                                noteForm.querySelector('input[name=title]').value = @json($note->title);
                                if(window.editor) window.editor.setData(@json($note->content));
                                noteForm.querySelector('input[name=is_public]').checked = {{ $note->is_public ? 'true' : 'false' }};
                            }
                        });
                    }
                    if(closeNoteFormBtn && noteFormModal) {
                        closeNoteFormBtn.addEventListener('click', function() {
                            noteFormModal.classList.add('hidden');
                        });
                    }
                    // Cancel button
                    var cancelBtn = document.getElementById('cancelNoteFormModal');
                    if(cancelBtn && noteFormModal) {
                        cancelBtn.addEventListener('click', function() {
                            noteFormModal.classList.add('hidden');
                        });
                    }
                });
                </script>
            @endif
        </div>
    </div>
    <!-- Kanan: Share & Komentar -->
    <div class="w-full md:w-96 flex flex-col gap-4">
        <!-- Kotak Atas: List User Share -->
        <div class="bg-gray-100 rounded-lg shadow p-4">
            <h2 class="font-semibold mb-2">Shared With</h2>
            <div class="flex flex-wrap gap-2 mb-2">
                @forelse($sharedUsers as $user)
                    <span class="bg-blue-200 text-blue-800 rounded-full px-3 py-1 text-xs flex items-center gap-2 mb-1">
                        {{ $user->email }}
                        @if(auth()->id() === $note->user_id)
                        <form action="{{ route('notes.unshare', [$note, $user]) }}" method="POST" class="inline-block ml-1 unshare-form" onsubmit="return confirm('Unshare note dari {{ $user->email }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs" title="Unshare">&times;</button>
                        </form>
                        @endif
                    </span>
                @empty
                    <span class="text-gray-400">Not shared to anyone</span>
                @endforelse
            </div>
            @if(auth()->id() === $note->user_id)
            <form action="{{ route('notes.share', $note) }}" method="POST" class="flex gap-2 items-center mt-2">
                @csrf
                <input type="email" name="email" id="shareEmailInput" class="border rounded px-2 py-1 flex-1" placeholder="Share to email..." required autocomplete="off">
                <div id="emailSuggestions" class="absolute bg-white border rounded shadow mt-1 z-50 hidden"></div>
                <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded">Share</button>
            </form>
            @if($errors->has('email'))
                <div class="text-red-500 text-xs mt-1">{{ $errors->first('email') }}</div>
            @endif
            @endif
        </div>
        <!-- Kotak Bawah: Komentar -->
        <div class="bg-gray-100 rounded-lg shadow p-4 flex-1 flex flex-col min-h-[200px]">
            <h2 class="font-semibold mb-2">Comments</h2>
            <div id="commentsListDetail" class="flex-1 overflow-y-auto max-h-56 mb-2">
                @forelse($comments as $c)
                    <div class="border rounded p-2 mb-2">
                        <div class="font-semibold">{{ $c->user->name ?? $c->user->email ?? 'User' }} <span class="text-xs text-gray-400">({{ $c->created_at->format('d-m-Y H:i') }})</span></div>
                        <div>{{ $c->content }}</div>
                    </div>
                @empty
                    <div class="text-gray-400 text-center">No comments yet.</div>
                @endforelse
            </div>
            <form action="{{ route('notes.addComment', $note) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="content" class="flex-1 border rounded px-2 py-1" placeholder="Write a comment..." required maxlength="1000">
                <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded">Send</button>
            </form>
        </div>
    </div>
</div>

{{-- Modal Profile --}}
@include('profile.edit', ['user' => Auth::user()])

@push('scripts')
<style>
    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateY(-10px); }
        10% { opacity: 1; transform: translateY(0); }
        90% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-10px); }
    }
    .animate-fade-in-out {
        animation: fadeInOut 2s;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Scroll komentar ke bawah
        var commentsList = document.getElementById('commentsListDetail');
        if (commentsList) {
            commentsList.scrollTop = commentsList.scrollHeight;
        }
        // Modal Profile
        var openProfileBtn = document.getElementById('openProfileModal');
        var profileModal = document.getElementById('profileModal');
        var closeProfileBtn = document.getElementById('closeProfileModal');
        if(openProfileBtn && profileModal) {
            openProfileBtn.addEventListener('click', function() {
                profileModal.classList.remove('hidden');
            });
        }
        if(closeProfileBtn && profileModal) {
            closeProfileBtn.addEventListener('click', function() {
                profileModal.classList.add('hidden');
            });
        }
        if(profileModal) {
            profileModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    profileModal.classList.add('hidden');
                }
            });
        }

        // Share URL button
        var shareBtn = document.getElementById('shareUrlBtn');
        var notif = document.getElementById('shareUrlNotif');
        if (shareBtn && notif) {
            shareBtn.addEventListener('click', function() {
                navigator.clipboard.writeText("{{ url('/public-notes/'.$note->id) }}");
                notif.classList.remove('hidden');
                notif.classList.add('animate-fade-in-out');
                setTimeout(function() {
                    notif.classList.add('hidden');
                    notif.classList.remove('animate-fade-in-out');
                }, 2000);
            });
        }
        // Email autocomplete for share
        var emailInput = document.getElementById('shareEmailInput');
        var suggestionsBox = document.getElementById('emailSuggestions');
        var selectedSuggestionIdx = -1;
        var suggestions = [];
        if(emailInput && suggestionsBox) {
            emailInput.addEventListener('input', function(e) {
                var query = this.value;
                selectedSuggestionIdx = -1;
                if(query.length < 2) {
                    suggestionsBox.classList.add('hidden');
                    suggestionsBox.innerHTML = '';
                    suggestions = [];
                    return;
                }
                fetch('/users/email-suggestions?q=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        suggestions = data;
                        if(data.length > 0) {
                            suggestionsBox.innerHTML = data.map((email, idx) => `<div class='px-3 py-2 hover:bg-blue-100 cursor-pointer' data-idx='${idx}'>${email}</div>`).join('');
                            suggestionsBox.classList.remove('hidden');
                        } else {
                            suggestionsBox.classList.add('hidden');
                            suggestionsBox.innerHTML = '';
                        }
                    });
            });
            emailInput.addEventListener('keydown', function(e) {
                if(suggestions.length === 0 || suggestionsBox.classList.contains('hidden')) return;
                if(e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedSuggestionIdx = (selectedSuggestionIdx + 1) % suggestions.length;
                    updateSuggestionHighlight();
                } else if(e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedSuggestionIdx = (selectedSuggestionIdx - 1 + suggestions.length) % suggestions.length;
                    updateSuggestionHighlight();
                } else if(e.key === 'Tab' || e.key === 'Enter') {
                    if(selectedSuggestionIdx >= 0 && selectedSuggestionIdx < suggestions.length) {
                        e.preventDefault();
                        emailInput.value = suggestions[selectedSuggestionIdx];
                        suggestionsBox.classList.add('hidden');
                    }
                }
            });
            function updateSuggestionHighlight() {
                Array.from(suggestionsBox.children).forEach((el, idx) => {
                    if(idx === selectedSuggestionIdx) {
                        el.classList.add('bg-blue-200');
                    } else {
                        el.classList.remove('bg-blue-200');
                    }
                });
            }
            suggestionsBox.addEventListener('mousedown', function(e) {
                if(e.target && e.target.textContent) {
                    emailInput.value = e.target.textContent;
                    suggestionsBox.classList.add('hidden');
                }
            });
            document.addEventListener('click', function(e) {
                if(!suggestionsBox.contains(e.target) && e.target !== emailInput) {
                    suggestionsBox.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
@endsection
