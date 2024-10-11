<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'slug',
        'content',
        'user_id',
        'image_url',
        'image_thumbnail_url',
        'categories',
    ];

    protected $casts = [
        'categories' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'blog_likes', 'blog_id', 'user_id')->withTimestamps();
    }

    public function getLikesCountAttribute()
    {
        return $this->likers->count();
    }
}
