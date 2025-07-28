@extends('layouts.app')

@section('content')
<nav class="bg-gray-100 rounded-lg shadow mb-6 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center space-x-2">
        <button id="tabMyNotes" class="tab-btn bg-blue-500 text-white px-4 py-2 rounded font-semibold relative">
            My Notes
            @php
                $myNotesCommentNotif = collect($notes)->filter(function($n) {
                    return $n->comments->where('is_read_owner', false)->where('user_id', '!=', Auth::id())->count() > 0;
                })->count();
            @endphp
            @if($myNotesCommentNotif > 0)
                <span id="myNotesCommentNotif" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $myNotesCommentNotif }}</span>
            @endif
        </button>
        <button id="tabSharedNotes" class="tab-btn bg-gray-200 text-gray-700 px-4 py-2 rounded font-semibold relative">
            Shared Notes
            @php
                $notifCount = collect($sharedNotes)->filter(function($n) {
                    return optional($n->pivot)->is_read === 0 || optional($n->pivot)->is_read === false;
                })->count();
                $sharedNotesCommentNotif = collect($sharedNotes)->filter(function($n) {
                    return $n->comments->where('is_read_shared', false)->where('user_id', '!=', Auth::id())->count() > 0;
                })->count();
            @endphp
            @if($notifCount > 0)
                <span id="sharedNotesNotif" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $notifCount }}</span>
            @endif
            @if($sharedNotesCommentNotif > 0)
                <span id="sharedNotesCommentNotif" class="absolute -top-2 right-6 bg-yellow-500 text-white text-xs rounded-full px-2 py-0.5">{{ $sharedNotesCommentNotif }}</span>
            @endif
        </button>
    </div>
    <div class="flex items-center space-x-4">
        <button id="openProfileModal" class="bg-blue-500 text-white px-4 py-2 rounded">Profile</button>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Logout</button>
        </form>
    </div>
</nav>

<div id="myNotesSection">
    <button id="openNoteFormModal" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">+ New Note</button>
    <div class="space-y-4">
        @forelse($notes as $note)
        <div class="border p-4 rounded">
            <div class="flex justify-between">
                <h2 class="text-xl font-semibold">{{ $note->title }}</h2>
                <div class="flex items-center space-x-2">
                    <button 
                        class="edit-note-btn bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded"
                        data-id="{{ $note->id }}"
                        data-title="{{ $note->title }}"
                        data-content="{{ $note->content }}"
                    >Edit</button>
                    <button 
                        class="share-note-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded flex items-center"
                        data-id="{{ $note->id }}"
                        data-title="{{ $note->title }}"
                    >
                        Share
                        @php
                            $sharedCount = $note->sharedWith()->count();
                        @endphp
                        <span class="ml-2 bg-white text-green-600 rounded-full px-2 py-0.5 text-xs font-bold">{{ $sharedCount }}</span>
                    </button>
                    <button class="open-comments-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded flex items-center" data-note-id="{{ $note->id }}" data-note-title="{{ $note->title }}">
                        Comments
                        @php
                            $unreadComments = $note->comments->where('is_read_owner', false)->where('user_id', '!=', Auth::id())->count();
                        @endphp
                        @if($unreadComments > 0)
                            <span class="ml-2 bg-red-500 text-white rounded-full px-2 py-0.5 text-xs font-bold comment-badge">{{ $unreadComments }}</span>
                        @endif
                    </button>
                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline delete-note-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded delete-note-btn">Delete</button>
                    </form>
                </div>
            </div>
            <div class="mt-2 prose max-w-none">
                {!! Str::limit($note->content, 200) !!}
            </div>
            <p class="text-gray-500 text-sm mt-2">
                Last updated: {{ $note->updated_at->diffForHumans() }}
            </p>
        </div>
        @empty
        <p class="text-gray-500">No notes found.</p>
        @endforelse
    </div>
</div>

<div id="sharedNotesSection" class="hidden">
    <div class="mb-4">
        <label class="mr-2 font-semibold">Filter by user:</label>
        <select id="sharedUserFilter" class="border rounded px-2 py-1">
            <option value="all">All</option>
            @php
                $sharedUsers = collect($sharedNotes)->pluck('owner')->filter()->unique('id');
            @endphp
            @foreach($sharedUsers as $user)
                <option value="user-{{ $user->id }}">{{ $user->email }}</option>
            @endforeach
        </select>
    </div>
    <div class="space-y-4" id="sharedNotesList">
        @forelse($sharedNotes as $note)
        <div class="border p-4 rounded shared-note-item" data-owner="user-{{ $note->owner->id ?? '' }}" data-note-id="{{ $note->id }}">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold flex items-center">{{ $note->title }}
                    @if(optional($note->pivot)->is_read === 0 || optional($note->pivot)->is_read === false)
                        <span class="ml-2 bg-blue-500 text-white text-xs px-2 py-0.5 rounded-full">new</span>
                    @elseif(optional($note->pivot)->is_updated === 1)
                        <span class="ml-2 bg-yellow-500 text-white text-xs px-2 py-0.5 rounded-full">updated</span>
                    @endif
                </h2>
                <div class="flex items-center space-x-2">
                    <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded">Shared by: {{ $note->owner->email ?? '-' }}</span>
                    {{-- tombol mark as read dihapus, sekarang auto mark as read saat buka tab --}}
                    <button class="open-comments-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded flex items-center" data-note-id="{{ $note->id }}" data-note-title="{{ $note->title }}">
                        Comments
                        @php
                            $unreadComments = $note->comments->where('is_read_shared', false)->where('user_id', '!=', Auth::id())->count();
                        @endphp
                        @if($unreadComments > 0)
                            <span class="ml-2 bg-red-500 text-white rounded-full px-2 py-0.5 text-xs font-bold comment-badge">{{ $unreadComments }}</span>
                        @endif
                    </button>
                </div>
            </div>
            <div class="mt-2 prose max-w-none">
                {!! Str::limit($note->content, 200) !!}
            </div>
            <p class="text-gray-500 text-sm mt-2">
                Last updated: {{ $note->updated_at->diffForHumans() }}
            </p>
        </div>
        @empty
        <p class="text-gray-500">No shared notes found.</p>
        @endforelse
    </div>
</div>

{{-- Modal Profile --}}
@include('profile.edit', ['user' => Auth::user()])

{{-- Modal Note Form --}}
@include('notes.form')

{{-- Modal Share Note --}}
@include('notes.share')

{{-- Modal Komentar --}}
<div id="commentsModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-lg p-6 relative">
        <button id="closeCommentsModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
        <h2 class="text-xl font-bold mb-4" id="commentsModalTitle">Comments</h2>
        <div id="commentsList" class="space-y-4 max-h-64 overflow-y-auto mb-4"></div>
        <form id="addCommentForm" class="flex space-x-2">
            <input type="text" name="content" class="flex-1 border rounded px-2 py-1" placeholder="Write a comment..." required maxlength="1000">
            <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded">Send</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        // Profile Modal
        $('#openProfileModal').on('click', function() {
            $('#profileModal').removeClass('hidden');
        });
        $('#closeProfileModal').on('click', function() {
            $('#profileModal').addClass('hidden');
        });
        $('#profileModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden');
            }
        });

        // Modal Note Form (CREATE)
        $('#openNoteFormModal').off('click').on('click', function() {
            var $form = $('#noteFormModal form');
            $form.trigger('reset');
            $form.attr('action', '/notes');
            $form.find('input[name="_method"]').remove();
            $form.find('input[name="title"]').val('');
            $form.find('textarea[name="content"]').val('');
            if (window.editor && typeof window.editor.setData === 'function') {
                window.editor.setData('');
            }
            // Set judul dan tombol
            $('#noteFormModalTitle').text('Create New Note');
            $('#noteFormSubmitBtn').text('Save Note');
            $('#noteFormModal').removeClass('hidden');
        });
        $('#closeNoteFormModal, #cancelNoteFormModal').on('click', function() {
            $('#noteFormModal').addClass('hidden');
        });
        $('#noteFormModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden');
            }
        });

        // Modal Edit Note (gunakan modal yang sama dengan create, pakai event delegation)
        $(document).off('click', '.edit-note-btn').on('click', '.edit-note-btn', function() {
            var noteId = $(this).data('id');
            var title = $(this).data('title');
            var content = $(this).data('content');
            var $form = $('#noteFormModal form');
            $form.attr('action', '/notes/' + noteId);
            $form.find('input[name="title"]').val(title);
            $form.find('textarea[name="content"]').val(content);
            if (window.editor && typeof window.editor.setData === 'function') {
                window.editor.setData(content);
            }
            if ($form.find('input[name="_method"]').length === 0) {
                $form.append('<input type="hidden" name="_method" value="PUT">');
            }
            // Set judul dan tombol
            $('#noteFormModalTitle').text('Edit Note');
            $('#noteFormSubmitBtn').text('Update Note');
            $('#noteFormModal').removeClass('hidden');
        });

        // Konfirmasi sebelum delete
        $('.delete-note-form').on('submit', function(e) {
            if (!confirm('Are you sure you want to delete this note?')) {
                e.preventDefault();
            }
        });
        // Share Note Modal: buka modal dan set noteId
        $(document).on('click', '.share-note-btn', function() {
            var noteId = $(this).data('id');
            window.currentNoteId = noteId;
            $('#shareNoteModal').removeClass('hidden');
            $('#shareNoteModal input[name="note_id"]').val(noteId);
        });

        // Unshare: update jumlah share dan tutup modal
        $(document).on('submit', '.unshare-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var noteId = $('#shareNoteModal input[name="note_id"]:first').val() || window.currentNoteId;
            if (!noteId) {
                alert('Note ID tidak ditemukan. Silakan refresh halaman.');
                return;
            }
            var $shareBtn = $('.share-note-btn[data-id="' + noteId + '"]');
            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                success: function() {
                    window.unshareConfirmed = false;
                    $.get('/notes/' + noteId + '/shared-users', function(data) {
                        $shareBtn.find('span').text(data.length);
                        $('#shareNoteModal').addClass('hidden');
                    });
                },
                error: function() {
                    window.unshareConfirmed = false;
                    $('#shareNoteModal').addClass('hidden');
                    alert('Gagal unshare. Silakan refresh halaman.');
                }
            });
        });

        // Tab switching
        $('#tabMyNotes').on('click', function() {
            $(this).addClass('bg-blue-500 text-white').removeClass('bg-gray-200 text-gray-700');
            $('#tabSharedNotes').addClass('bg-gray-200 text-gray-700').removeClass('bg-blue-500 text-white');
            $('#myNotesSection').show();
            $('#sharedNotesSection').hide();
        });
        $('#tabSharedNotes').on('click', function() {
            $(this).addClass('bg-blue-500 text-white').removeClass('bg-gray-200 text-gray-700');
            $('#tabMyNotes').addClass('bg-gray-200 text-gray-700').removeClass('bg-blue-500 text-white');
            $('#myNotesSection').hide();
            $('#sharedNotesSection').show();
            // Mark all unread shared notes as read (hanya update DB, label 'new' tetap sampai reload)
            var unreadNoteIds = [];
            $('#sharedNotesList .shared-note-item').each(function() {
                var $item = $(this);
                if ($item.find('.bg-blue-500').length) {
                    unreadNoteIds.push($item.data('note-id'));
                }
            });
            if(unreadNoteIds.length > 0) {
                unreadNoteIds.forEach(function(noteId) {
                    $.ajax({
                        url: '/notes/' + noteId + '/mark-as-read',
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' }
                    });
                });
                // Update badge notif
                setTimeout(function() {
                    $('#sharedNotesNotif').hide();
                }, 400);
            } else {
                $('#sharedNotesNotif').hide();
            }
        });

        // Filter shared notes by user
        $('#sharedUserFilter').on('change', function() {
            var val = $(this).val();
            if(val === 'all') {
                $('#sharedNotesList .shared-note-item').show();
            } else {
                $('#sharedNotesList .shared-note-item').hide();
                $('#sharedNotesList .shared-note-item[data-owner="' + val + '"]').show();
            }
        });

        // Mark as Read AJAX
        $(document).on('click', '.mark-as-read-btn', function() {
            var $btn = $(this);
            var noteId = $btn.data('note-id');
            $.ajax({
                url: '/notes/' + noteId + '/mark-as-read',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function() {
                    $btn.prev('.bg-blue-500').remove();
                    $btn.remove();
                    // Update badge notif
                    var $badge = $('#sharedNotesNotif');
                    var count = parseInt($badge.text() || '1', 10) - 1;
                    if(count > 0) {
                        $badge.text(count);
                    } else {
                        $badge.hide();
                    }
                }
            });
        });

        // Komentar: buka modal, fetch, tampilkan, mark as read
        var currentCommentsNoteId = null;
        function openCommentsModal(noteId, noteTitle, $btn) {
            currentCommentsNoteId = noteId;
            $('#commentsModalTitle').text('Comments for: ' + noteTitle);
            $('#commentsModal').removeClass('hidden');
            $('#commentsList').html('<div class="text-gray-400 text-center">Loading...</div>');
            // Fetch comments
            $.get('/notes/' + noteId + '/comments', function(comments) {
                renderComments(comments);
            });
            // Mark comments as read
            $.post('/notes/' + noteId + '/comments/mark-read', {_token: '{{ csrf_token() }}'});
            // Hilangkan badge notif di tombol
            if ($btn) $btn.find('.comment-badge').remove();
            // Update badge notif di tab
            setTimeout(function() {
                // My Notes
                var myNotesCount = 0;
                $('.open-comments-btn').each(function() {
                    if ($(this).closest('#myNotesSection').length && $(this).find('.comment-badge').length) {
                        myNotesCount++;
                    }
                });
                if(myNotesCount > 0) {
                    $('#myNotesCommentNotif').text(myNotesCount).show();
                } else {
                    $('#myNotesCommentNotif').hide();
                }
                // Shared Notes
                var sharedNotesCount = 0;
                $('.open-comments-btn').each(function() {
                    if ($(this).closest('#sharedNotesSection').length && $(this).find('.comment-badge').length) {
                        sharedNotesCount++;
                    }
                });
                if(sharedNotesCount > 0) {
                    $('#sharedNotesCommentNotif').text(sharedNotesCount).show();
                } else {
                    $('#sharedNotesCommentNotif').hide();
                }
            }, 300);
        }
        $(document).on('click', '.open-comments-btn', function() {
            var noteId = $(this).data('note-id');
            var noteTitle = $(this).data('note-title');
            openCommentsModal(noteId, noteTitle, $(this));
        });
        $('#closeCommentsModal').on('click', function() {
            $('#commentsModal').addClass('hidden');
            currentCommentsNoteId = null;
        });
        $('#commentsModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden');
                currentCommentsNoteId = null;
            }
        });
        // Submit komentar
        $('#addCommentForm').on('submit', function(e) {
            e.preventDefault();
            var content = $(this).find('input[name="content"]').val();
            if (!content.trim() || !currentCommentsNoteId) return;
            $.post('/notes/' + currentCommentsNoteId + '/comments', {
                _token: '{{ csrf_token() }}',
                content: content
            }, function(comment) {
                $('#addCommentForm')[0].reset();
                $.get('/notes/' + currentCommentsNoteId + '/comments', function(comments) {
                    renderComments(comments);
                });
            });
        });
        function renderComments(comments) {
            if (!comments.length) {
                $('#commentsList').html('<div class="text-gray-400 text-center">No comments yet.</div>');
                return;
            }
            var html = '';
            comments.forEach(function(c) {
                html += '<div class="border rounded p-2">';
                html += '<div class="font-semibold">' + (c.user?.name || c.user?.email || 'User') + ' <span class="text-xs text-gray-400">(' + (new Date(c.created_at)).toLocaleString() + ')</span></div>';
                html += '<div>' + $('<div>').text(c.content).html() + '</div>';
                html += '</div>';
            });
            $('#commentsList').html(html);
        }
        // ESC key closes all modals
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('#profileModal').addClass('hidden');
                $('#noteFormModal').addClass('hidden');
                $('#shareNoteModal').addClass('hidden');
                $('#commentsModal').addClass('hidden');
            }
        });
    });
</script>
@endpush