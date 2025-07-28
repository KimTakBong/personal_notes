<!-- Modal Profile, panggil dari index notes -->
<div id="profileModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow p-6 max-w-xl w-full mx-4 relative">
        <button id="closeProfileModal" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

        @if(session('status') === 'profile-updated')
            <div class="mb-4 text-green-600">Profile updated successfully.</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                    class="w-full px-3 py-2 border rounded" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                    class="w-full px-3 py-2 border rounded" required>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Profile</button>
        </form>

        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6">
            @csrf
            @method('DELETE')
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-3 py-2 border rounded" required placeholder="Enter your password to confirm">
                @error('userDeletion.password')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded"
                onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</button>
        </form>
    </div>
</div>