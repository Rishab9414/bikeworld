<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\SeoService;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::published()->latest('published_at')->paginate(9);

        $seo = app(SeoService::class)->resolve([
            'title' => 'Blog — Riding Tips & Bike Gear Guides | '.config('seo.site_name'),
            'description' => 'Expert guides on helmets, riding gear, bike care and accessories. Tips for Indian riders from BikeWorld.',
            'keywords' => 'bike accessories blog, motorcycle riding tips, helmet guide india, bike care blog, BikeWorld blog',
            'canonical' => route('blog.index'),
        ]);

        return view('shop.blog.index', compact('posts', 'seo'));
    }

    public function show(BlogPost $post): View
    {
        abort_unless($post->status === 'published' && $post->published_at && $post->published_at <= now(), 404);

        $post->increment('views');

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        $seo = app(SeoService::class)->resolve([
            'title' => ($post->meta_title ?: $post->title).' | '.config('seo.site_name'),
            'description' => $post->meta_description ?: ($post->excerpt ?: strip_tags(substr($post->content, 0, 160))),
            'keywords' => $post->meta_keywords ?: config('seo.default_keywords'),
            'image' => $post->imageUrl(),
            'canonical' => route('blog.show', $post),
            'type' => 'article',
        ]);

        return view('shop.blog.show', compact('post', 'related', 'seo'));
    }
}
