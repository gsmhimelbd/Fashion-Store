<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryPageController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return view('categories', compact('categories'));
    }
}
