<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
         Category::create([
             'name' => $request->name,
         ]);

         return response()->json(['message' => 'Kategória bola úspešne vytvorená'], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategória nebola nájdená.'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['category' => $category], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategória sa nenašla.'], Response::HTTP_NOT_FOUND);
        }
        $category->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Kategória bola úspešne upravená'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Kategória sa nenašla.'], Response::HTTP_NOT_FOUND);
        }
        $category->delete();
        return response()->json(['message' => 'Kategória bola úspešne zmazaná'], Response::HTTP_OK);
    }
}
