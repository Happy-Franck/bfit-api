<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    // Public listing for challenger and admin use (filtered by published for non-admin)
    public function index(Request $request)
    {
        $blogs = Blog::query()
            ->with('user');

        if (!Auth::user()->hasRole('administrateur')) {
            $blogs->where('published', true);
        }

        // Optional search on title, excerpt, or slug
        if ($request->filled('q')) {
            $term = $request->input('q');
            $blogs->where(function ($query) use ($term) {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        $blogs = $blogs->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json($blogs);
    }

    public function show(Blog $blog)
    {
        return response()->json($blog->load('user'));
    }

    // Admin: create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:50'],
            'content' => ['required', 'string'],
            'published' => ['sometimes', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $blog = new Blog($validated);
        $blog->user_id = Auth::id();
        $blog->save();

        // Auto set published_at to created_at when published
        if ($blog->published && empty($blog->published_at)) {
            $blog->published_at = $blog->created_at;
            $blog->save();
        }

        return response()->json($blog->load('user'), 201);
    }

    // Admin: update
    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blog->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:50'],
            'published' => ['boolean'],
            'content' => ['required', 'string'],
        ]);

        // If slug is intentionally sent empty, regenerate from title
        if (array_key_exists('slug', $validated) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $wasPublished = $blog->published;

        $blog->update($validated);

        // If toggled to published and no published_at yet, set it to created_at
        if (!$wasPublished && $blog->published && empty($blog->published_at)) {
            $blog->published_at = $blog->created_at;
            $blog->save();
        }

        return response()->json($blog->load('user'));
    }

    // Admin: delete
    public function destroy(Blog $blog)
    {
        $blog->delete();
        return response()->json(['message' => 'Deleted']);
    }

} 