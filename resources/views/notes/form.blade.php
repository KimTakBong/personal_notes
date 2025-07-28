<!-- Modal Note Form -->
<div id="noteFormModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow p-6 max-w-4xl w-full mx-4 relative">
        <button id="closeNoteFormModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        <h1 id="noteFormModalTitle" class="text-2xl font-bold mb-6">
            Create New Note
        </h1>
        
        <form method="POST" action="{{ route('notes.store') }}">
            @csrf
            @if(isset($note))
                @method('PUT')
            @endif

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Title</label>
                <input type="text" name="title" value="{{ old('title', $note->title ?? '') }}" class="w-full px-3 py-2 border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Content</label>
                <textarea id="content" name="content" rows="20"
                    class="w-full px-3 py-2 border rounded min-h-[300px] min-w-full">{{ old('content', $note->content ?? '') }}</textarea>
            </div>
            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public', $note->is_public ?? false) ? 'checked' : '' }}>
                <label for="is_public" class="font-semibold">Public Note</label>
                <span class="text-xs text-gray-400">(Jika dicentang, note bisa diakses publik via URL)</span>
            </div>

            <button id="noteFormSubmitBtn" type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                Save Note
            </button>
            <button type="button" id="cancelNoteFormModal" class="bg-gray-300 text-gray-800 px-4 py-2 rounded ml-2 inline-block">Cancel</button>
        </form>
    </div>
</div>

@push('scripts')
<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.2.0/classic/ckeditor.js"></script>
<script>
    if(document.getElementById('content')) {
        ClassicEditor
            .create(document.querySelector('#content'))
            .then(editor => {
                window.editor = editor;
            })
            .catch(error => {
                console.error(error);
            });
    }
</script>
@endpush

@push('styles')
<style>
    /* Untuk jQuery UI Autocomplete atau plugin lain */
    .ui-autocomplete, .autocomplete-suggestions {
        min-width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
        left: 0 !important;
    }
    /* Agar suggestion tidak lebih lebar dari input */
    .ui-autocomplete, .autocomplete-suggestions {
        width: 100% !important;
    }
</style>
@endpush