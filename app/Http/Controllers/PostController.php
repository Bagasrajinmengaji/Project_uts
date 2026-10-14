<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of published posts.
     * Supports query params: ?category={slug}&search={keyword}
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::query()
            ->where('status', 'published')
            ->with(['user:id,name', 'category:id,name,slug']);

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->latest()->paginate($request->integer('per_page', 9));

        return response()->json($posts);
    }

    /**
     * Display post detail by slug.
     */
    public function showBySlug(Request $request, string $slug): JsonResponse
    {
        $post = Post::where('slug', $slug)
            ->with(['user:id,name', 'category:id,name,slug'])
            ->firstOrFail();

        // If draft, only the author can view it
        if ($post->status !== 'published') {
            $user = $request->user('sanctum');
            if (! $user || $user->id !== $post->user_id) {
                abort(404, 'Berita tidak ditemukan.');
            }
        }

        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Display posts created by current authenticated user.
     */
    public function myPosts(Request $request): JsonResponse
    {
        $posts = Post::query()
            ->where('user_id', $request->user()->id)
            ->with('category:id,name,slug')
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($posts);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'status' => ['nullable', 'in:published,draft'],
        ]);

        $slug = $this->generateUniqueSlug($validated['title']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'image' => $imagePath,
            'status' => $validated['status'] ?? 'published',
        ]);

        $post->load(['user:id,name', 'category:id,name,slug']);

        return response()->json([
            'message' => 'Berita berhasil dibuat.',
            'post' => $post,
        ], 201);
    }

    /**
     * Display a specific post by ID.
     */
    public function show(int $id): JsonResponse
    {
        $post = Post::with(['user:id,name', 'category:id,name,slug'])->findOrFail($id);

        return response()->json([
            'post' => $post,
        ]);
    }

    /**
     * Update an existing post.
     * Enforces PostPolicy::update (403 Forbidden if not owner).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check policy
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'status' => ['required', 'in:published,draft'],
        ]);

        // If title changed, update slug
        if ($post->title !== $validated['title']) {
            $post->slug = $this->generateUniqueSlug($validated['title'], $post->id);
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($post->image && Storage::disk('public')->exists($post->image)) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('posts', 'public');
        }

        $post->title = $validated['title'];
        $post->category_id = $validated['category_id'];
        $post->content = $validated['content'];
        $post->status = $validated['status'];
        $post->save();

        $post->load(['user:id,name', 'category:id,name,slug']);

        return response()->json([
            'message' => 'Berita berhasil diperbarui.',
            'post' => $post,
        ]);
    }

    /**
     * Remove the specified post.
     * Enforces PostPolicy::delete (403 Forbidden if not owner).
     */
    public function destroy(int $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        // Check policy
        Gate::authorize('delete', $post);

        if ($post->image && Storage::disk('public')->exists($post->image)) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return response()->json([
            'message' => 'Berita berhasil dihapus.',
        ]);
    }

    /**
     * Helper to generate a unique slug for post.
     */
    protected function generateUniqueSlug(string $title, ?int $exceptId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (
            Post::where('slug', $slug)
                ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$originalSlug}-" . Str::random(5);
            $counter++;
            if ($counter > 10) {
                $slug = "{$originalSlug}-" . time();
                break;
            }
        }

        return $slug;
    }
}
