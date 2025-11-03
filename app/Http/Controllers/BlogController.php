<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // BLOG-CREATE-API
    public function store(BlogRequest $request)
    {
        $validated = $request->validated();
        $imagePath = $request->file('image')->store('blogs', 'public');

        $blog = Blog::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'user_id' => Auth::id(),
        ]);

        return response()->json($blog, 201);
    }

    // BLOG-LIST-API (with pagination, filters, search, is_liked)
    public function index(Request $request)
    {
        $query = Blog::withCount('likes'); // For likes count

        // Search in title/description
        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        // Filters: most liked or latest
        $sort = $request->query('sort', 'latest');
        if ($sort === 'most_liked') {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination (10 per page)
        $blogs = $query->paginate(10);

        // Add is_liked for logged-in user (using polymorphic)
        $userId = Auth::id();
        $blogs->getCollection()->transform(function ($blog) use ($userId) {
            $blog->is_liked = $blog->likes->contains('user_id', $userId);
            return $blog;
        });

        return response()->json($blogs);
    }

    // BLOG-EDIT-API
    public function update(BlogRequest $request, $id)
    {
        $blog = Blog::findOrFail($id);
        if ($blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($blog->image_path);
            $validated['image_path'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($validated);

        return response()->json($blog);
    }

    // BLOG-DELETE-API
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        if ($blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($blog->image_path) {
            Storage::disk('public')->delete($blog->image_path);
        }

        $blog->delete();

        return response()->json(['message' => 'Blog deleted'], 200);
    }

    // BLOG-LIKE-TOGGLE
    public function toggleLike($id)
    {
        $blog = Blog::findOrFail($id);
        $userId = Auth::id();

        $like = Like::where('likeable_id', $id)
            ->where('likeable_type', Blog::class)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'Unliked']);
        }

        Like::create([
            'likeable_id' => $id,
            'likeable_type' => Blog::class,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Liked']);
    }
}