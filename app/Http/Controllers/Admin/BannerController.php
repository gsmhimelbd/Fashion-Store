<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('order')->latest()->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:200',
            'badge_text' => 'nullable|string|max:100',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $filename = 'banner_' . time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->move(public_path('uploads'), $filename);

        Banner::create([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'badge_text' => $validated['badge_text'],
            'button_text' => $validated['button_text'] ?: 'Shop Now',
            'button_url' => $validated['button_url'] ?: '/shop',
            'image_path' => 'uploads/' . $filename,
            'is_active' => $request->boolean('is_active', true),
            'order' => Banner::count() + 1,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner uploaded successfully.');
    }

    public function update(Request $request, int $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:200',
            'badge_text' => 'nullable|string|max:100',
            'button_text' => 'nullable|string|max:50',
            'button_url' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'badge_text' => $validated['badge_text'],
            'button_text' => $validated['button_text'] ?: 'Shop Now',
            'button_url' => $validated['button_url'] ?: '/shop',
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('image')) {
            $filename = 'banner_' . time() . '_' . uniqid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('uploads'), $filename);
            $updateData['image_path'] = 'uploads/' . $filename;
        }

        $banner->update($updateData);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function toggle(int $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->update(['is_active' => !$banner->is_active]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner status updated.');
    }

    public function destroy(int $id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }
}
