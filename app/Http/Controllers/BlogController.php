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

        // Ensure public access does not call methods on null user
        $user = Auth::user();
        if (!$user || !$user->hasRole('administrateur')) {
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
        $user = Auth::user();
        if (!$user || !$user->hasRole('administrateur')) {
            if (!$blog->published) {
                abort(404);
            }
        }
        return response()->json($blog->load('user'));
    }

    // Admin: create
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'type' => ['nullable', 'in:tofu,mofu,bofu'],
            'content' => ['required', 'string'],
            'published' => ['sometimes'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Gestion de l'upload d'image
        $image_name = null;
        if ($request->hasFile('image')) {
            $image_name = $request->image->getClientOriginalName();
            $request->image->storeAs('blogs', $image_name, 'public');
        }

        // Convertir published en boolean
        $validated['published'] = filter_var($validated['published'] ?? false, FILTER_VALIDATE_BOOLEAN);
        
        $blog = new Blog($validated);
        $blog->user_id = Auth::id();
        $blog->image = $image_name;
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
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'type' => ['nullable', 'in:tofu,mofu,bofu'],
            'published' => ['sometimes'],
            'content' => ['required', 'string'],
        ]);

        // If slug is intentionally sent empty, regenerate from title
        if (array_key_exists('slug', $validated) && empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Gestion de l'upload d'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($blog->image) {
                $oldImagePath = storage_path('app/public/blogs/' . $blog->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $image_name = $request->image->getClientOriginalName();
            $request->image->storeAs('blogs', $image_name, 'public');
            $validated['image'] = $image_name;
        }

        // Convertir published en boolean
        if (isset($validated['published'])) {
            $validated['published'] = filter_var($validated['published'], FILTER_VALIDATE_BOOLEAN);
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
        // Supprimer l'image associée si elle existe
        if ($blog->image) {
            $imagePath = storage_path('app/public/blogs/' . $blog->image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $blog->delete();
        return response()->json(['message' => 'Deleted']);
    }

} 