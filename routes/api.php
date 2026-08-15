<?php

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/products', function (Request $request) {
    return Product::with(['category', 'images'])->where('is_active', true)->paginate(12);
});

Route::get('/products/{id}', function ($id) {
    return Product::with(['category', 'images', 'reviews'])->findOrFail($id);
});
