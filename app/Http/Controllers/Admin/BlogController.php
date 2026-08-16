<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->paginate(15);
        return view('admin.blogs.index', compact('blogs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug',
            'category' => 'required|string|max:100',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author' => 'nullable|string|max:100',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'is_published' => 'nullable|boolean',
        ]);

        $imagePath = 'uploads/hero-banner-1.svg';
        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $imagePath = 'uploads/' . $filename;
        } elseif ($request->filled('existing_image')) {
            $imagePath = $request->input('existing_image');
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);

        Blog::create([
            'title' => $validated['title'],
            'slug' => $slug . '-' . rand(100, 999),
            'category' => $validated['category'],
            'summary' => $validated['summary'],
            'content' => $validated['content'],
            'author' => $validated['author'] ?: 'OnlineBdMart Editorial',
            'image_path' => $imagePath,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog article published with cover photo successfully.');
    }

    public function update(Request $request, int $id)
    {
        $blog = Blog::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'summary' => 'nullable|string|max:500',
            'content' => 'required|string',
            'author' => 'nullable|string|max:100',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'is_published' => 'nullable|boolean',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'category' => $validated['category'],
            'summary' => $validated['summary'],
            'content' => $validated['content'],
            'author' => $validated['author'] ?: 'OnlineBdMart Editorial',
            'is_published' => $request->boolean('is_published'),
        ];

        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            $filename = 'blog_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $updateData['image_path'] = 'uploads/' . $filename;
        } elseif ($request->filled('existing_image')) {
            $updateData['image_path'] = $request->input('existing_image');
        }

        $blog->update($updateData);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog article updated successfully.');
    }

    public function destroy(int $id)
    {
        $blog = Blog::findOrFail($id);
        $blog->delete();

        return redirect()->route('admin.blogs.index')->with('success', 'Blog article deleted.');
    }
}
