<!-- Modal Share Note -->
<div id="shareNoteModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow p-6 max-w-lg w-full mx-4 relative">
        <button id="closeShareNoteModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        <h1 class="text-2xl font-bold mb-6">Share Note</h1>
        <form id="shareNoteForm" method="POST">
            @csrf
            <label class="block text-gray-700 mb-2">Share to (email):</label>
            <input type="email" name="email" id="shareEmailInput" class="w-full px-3 py-2 border rounded" required autocomplete="off">
            <ul id="emailSuggestions" class="border rounded bg-white mt-1 hidden absolute z-10 w-full"></ul>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mt-2">Share</button>
        </form>
        <div class="mt-6">
            <h2 class="font-semibold mb-2">Shared Users:</h2>
            <ul id="sharedUsersList" class="space-y-2"></ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentNoteId = null;

    // Modal Share Note logic
    $('.share-note-btn').on('click', function() {
        currentNoteId = $(this).data('id');
        $('#shareNoteForm').attr('action', '/notes/' + currentNoteId + '/share');
        $('#shareNoteModal').removeClass('hidden');
        loadSharedUsers(currentNoteId);
    });

    $('#closeShareNoteModal').on('click', function() {
        $('#shareNoteModal').addClass('hidden');
    });
    $('#shareNoteModal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
        }
    });

    // Load shared users with unshare button
    function loadSharedUsers(noteId) {
        $.get('/notes/' + noteId + '/shared-users', function(data) {
            let $list = $('#sharedUsersList');
            $list.empty();
            if (data.length === 0) {
                $list.append('<li class="text-gray-500">No shared users.</li>');
            } else {
                $.each(data, function(i, user) {
                    $list.append(
                        `<li class="flex justify-between items-center">
                            <span>${user.email}</span>
                            <form method="POST" action="/notes/${noteId}/unshare/${user.id}" class="unshare-form inline ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 text-xs px-2 py-1 rounded hover:underline">Unshare</button>
                            </form>
                        </li>`
                    );
                });
            }
        });
    }

    // AJAX unshare
    $(document).on('submit', '.unshare-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        if (!confirm('Unshare this user?')) return;
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            success: function() {
                loadSharedUsers(currentNoteId);
            }
        });
    });

    // Email autocomplete for share modal
    let selectedSuggestion = -1;
    let suggestions = [];
    $('#shareEmailInput').on('input', function() {
        let query = $(this).val();
        if (query.length < 2) {
            $('#emailSuggestions').empty().addClass('hidden');
            return;
        }
        $.get('/users/email-suggestions', { q: query }, function(data) {
            suggestions = data;
            let $list = $('#emailSuggestions');
            $list.empty();
            if (data.length === 0) {
                $list.addClass('hidden');
            } else {
                data.forEach(function(email, idx) {
                    $list.append('<li class="px-3 py-2 cursor-pointer suggestion-item" data-idx="'+idx+'">'+email+'</li>');
                });
                $list.removeClass('hidden');
                selectedSuggestion = -1;
            }
        });
    });

    // Keyboard navigation for suggestions
    $('#shareEmailInput').on('keydown', function(e) {
        let $items = $('#emailSuggestions .suggestion-item');
        if ($items.length === 0) return;
        if (e.key === 'ArrowDown') {
            selectedSuggestion = Math.min(selectedSuggestion + 1, $items.length - 1);
            $items.removeClass('bg-blue-100');
            $items.eq(selectedSuggestion).addClass('bg-blue-100');
            e.preventDefault();
        } else if (e.key === 'ArrowUp') {
            selectedSuggestion = Math.max(selectedSuggestion - 1, 0);
            $items.removeClass('bg-blue-100');
            $items.eq(selectedSuggestion).addClass('bg-blue-100');
            e.preventDefault();
        } else if (e.key === 'Tab' || e.key === 'Enter') {
            if (selectedSuggestion >= 0) {
                $('#shareEmailInput').val($items.eq(selectedSuggestion).text());
                $('#emailSuggestions').empty().addClass('hidden');
                e.preventDefault();
            }
        }
    });

    // Click suggestion
    $(document).on('click', '.suggestion-item', function() {
        $('#shareEmailInput').val($(this).text());
        $('#emailSuggestions').empty().addClass('hidden');
    });

    // Hide suggestions on blur
    $('#shareEmailInput').on('blur', function() {
        setTimeout(function() {
            $('#emailSuggestions').empty().addClass('hidden');
        }, 100);
    });
</script>
@endpush