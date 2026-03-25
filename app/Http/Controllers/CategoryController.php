<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::query()->orderByDesc('updated_at')->get();
        return response()->json(['categories' => $categories], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validation = $request->validate([
             'name' => ['required', 'string', 'min:3', 'max:255', 'unique:categories,name'],
             'color' => ['required', 'string', 'min:3', 'max:255']
         ]);

         Category::create([
             'name' => $validation['name'],
             'color' => $validation['color'],
         ]);

         return response()->json(['message' => 'Kategória bola úspešne vytvorená'], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json(['category' => $category], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validation = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255',
                Rule::unique('categories', 'name')->ignore($category)],
            'color' => ['required', 'string', 'min:3', 'max:255']
        ]);
        $category->update([
            'name' => $validation['name'],
            'color' => $validation['color'],
        ]);

        return response()->json(['message' => 'Kategória bola úspešne upravená'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Kategória bola úspešne zmazaná'], Response::HTTP_OK);
    }
}
