<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Blog::where('is_published', true)->latest()->paginate(6);
        return view('blog.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Blog::where('slug', $slug)->where('is_published', true)->firstOrFail();
        $recentPosts = Blog::where('id', '!=', $post->id)->where('is_published', true)->latest()->take(3)->get();
        return view('blog.show', compact('post', 'recentPosts'));
    }
}
