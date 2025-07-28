<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['title', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function sharedWith()
    {
        return $this->belongsToMany(User::class, 'note_user')
            ->withPivot('is_read', 'is_updated', 'last_shared_at');
    }
    
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
