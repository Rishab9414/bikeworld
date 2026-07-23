<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::with('author')->latest()->paginate(20);

        return view('admin.blog.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog.form', [
            'post' => new BlogPost(['status' => 'draft', 'published_at' => now()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePost($request);
        $data['author_id'] = Auth::id();

        if ($request->hasFile('featured_image_file')) {
            $data['featured_image'] = $request->file('featured_image_file')->store('blog', 'public');
        }

        unset($data['featured_image_file']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);
        ActivityLogger::log('created', 'blog', $post, "Blog post {$post->title} created");

        return redirect()->route('admin.blog.index')->with('success', 'Blog post created.');
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.blog.form', compact('post'));
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validatePost($request, $post);

        if ($request->hasFile('featured_image_file')) {
            if ($post->featured_image && ! str_starts_with($post->featured_image, 'http')) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image_file')->store('blog', 'public');
        }

        unset($data['featured_image_file']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $post->published_at ?? now();
        }

        $post->update($data);
        ActivityLogger::log('updated', 'blog', $post, "Blog post {$post->title} updated");

        return redirect()->route('admin.blog.index')->with('success', 'Blog post updated.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        if ($post->featured_image && ! str_starts_with($post->featured_image, 'http')) {
            Storage::disk('public')->delete($post->featured_image);
        }

        ActivityLogger::log('deleted', 'blog', $post, "Blog post {$post->title} deleted");
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Blog post deleted.');
    }

    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug,'.($post?->id ?? 'NULL')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string', 'max:500'],
            'featured_image_file' => ['nullable', 'image', 'max:4096'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
