<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Note extends Model
{
    protected $fillable = ['title', 'content', 'is_public'];
    public $incrementing = false;
    protected $keyType = 'string';
    // Alias for sharedWith, for compatibility with controller code
    public function sharedUsers()
    {
        return $this->sharedWith();
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

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
