<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'image',
        'type',
        'published',
        'published_at',
        'content',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Use slug for route model binding where appropriate
    public function getRouteKeyName()
    {
        return 'slug';
    }
} 