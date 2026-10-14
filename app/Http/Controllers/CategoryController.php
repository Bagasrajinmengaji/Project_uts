<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount(['posts' => function ($query) {
            $query->where('status', 'published');
        }])->orderBy('name')->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json([
            'category' => $category,
        ]);
    }
}
