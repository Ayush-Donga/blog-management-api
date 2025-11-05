<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image_path', 'user_id'];

    // === RELATIONSHIPS ===
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    // === ACCESSORS (Dynamic Attributes) ===
    protected $appends = ['is_liked', 'image_url'];

    // Dynamic: is_liked for current logged-in user
    public function getIsLikedAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->likes()
            ->where('user_id', Auth::id())
            ->exists(); // Fast, uses index, no N+1
    }

    // Dynamic: full image URL
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }
}