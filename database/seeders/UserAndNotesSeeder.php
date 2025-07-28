<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

class UserAndNotesSeeder extends Seeder
{
    public function run(): void
    {
        // Create 3 users
        $users = User::factory()->count(3)->create();

        // For each user, create 3 notes (mix public/private)
        foreach ($users as $index => $user) {
            for ($i = 1; $i <= 3; $i++) {
                $note = Note::create([
                    'id' => Str::uuid(),
                    'user_id' => $user->id,
                    'title' => "Note {$i} for {$user->name}",
                    'content' => "This is note {$i} for {$user->name}.",
                    'is_public' => $i % 2 === 0, // Alternate public/private
                ]);
                // Attach to owner in pivot
                DB::table('note_user')->insert([
                    'note_id' => $note->id,
                    'user_id' => $user->id,
                    'is_read' => false,
                    'is_updated' => false,
                    'last_shared_at' => now(),
                ]);
                // Share some notes to another user
                if ($i === 2 && isset($users[$index + 1])) {
                    DB::table('note_user')->insert([
                        'note_id' => $note->id,
                        'user_id' => $users[$index + 1]->id,
                        'is_read' => false,
                        'is_updated' => false,
                        'last_shared_at' => now(),
                    ]);
                }
            }
        }
    }
}
