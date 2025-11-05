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
        $userId = Auth::id();

        $query = Blog::query()
            ->withCount('likes')
            ->withExists([
                'likes as is_liked' => fn($q) => $q->where('user_id', $userId)
            ]);

        if ($search = $request->query('search')) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        $sort = $request->query('sort', 'latest');
        $query->orderBy($sort === 'most_liked' ? 'likes_count' : 'created_at', 'desc');

        $blogs = $query->paginate(10);

        $blogs->getCollection()->transform(fn($blog) => tap($blog, function ($blog) {
            $blog->image_url = $blog->image_path ? asset('storage/' . $blog->image_path) : null;
        }));

        return response()->json($blogs);
    }

    // BLOG-EDIT-API
    public function update(BlogRequest $request, $id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $updateData = [];

        if ($request->has('title')) {
            $updateData['title'] = $request->input('title');
        }
        if ($request->has('description')) {
            $updateData['description'] = $request->input('description');
        }
        if ($request->hasFile('image')) {
            if ($blog->image_path && Storage::disk('public')->exists($blog->image_path)) {
                Storage::disk('public')->delete($blog->image_path);
            }
            $updateData['image_path'] = $request->file('image')->store('blogs', 'public');
        } elseif ($request->has('image') && $request->input('image') !== null) {
            // Allow updating image_path with a string value
            $updateData['image_path'] = $request->input('image');
        }

        if (!empty($updateData)) {
            $blog->update($updateData);
        }

        return response()->json($blog->fresh()->loadCount('likes'));
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