@extends('layouts.app')

@section('content')

<div class="flex flex-col md:flex-row gap-6">
    <div class="flex-1 bg-white rounded-lg shadow p-6 flex flex-col">
        <h1 class="text-2xl font-bold mb-2 break-words">{{ $note->title }}</h1>
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
        </div>
    </div>
    <div class="w-full md:w-96 flex flex-col gap-4">
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
            @auth
            <form action="{{ route('notes.addComment', $note) }}" method="POST" class="flex gap-2">
                @csrf
                <input type="text" name="content" class="flex-1 border rounded px-2 py-1" placeholder="Write a comment..." required maxlength="1000">
                <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded">Send</button>
            </form>
            @else
            <div class="text-xs text-gray-500 text-center mt-2">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login untuk menambah komentar</a>.
            </div>
            @endauth
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var commentsList = document.getElementById('commentsListDetail');
        if (commentsList) {
            commentsList.scrollTop = commentsList.scrollHeight;
        }
    });
</script>
@endpush
@endsection
