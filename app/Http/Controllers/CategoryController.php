<?php

namespace App\Http\Controllers;

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
        $categories = DB::table('categories')
                      ->orderby('categories.updated_at', 'desc')
                      ->select('categories.*')
                      ->get();

        return response()->json(['categories' => $categories], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         DB::table('categories')->insert([
             'name' => $request->name,
             'created_at' => now(),
             'updated_at' => now()
         ]);

         return response()->json(['message' => 'Kategória bola úspešne vytvorená'], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = DB::table('categories')
                    ->where('categories.id', $id)
                    ->first();
        return response()->json(['category' => $category], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::table('categories')->where('id', $id)->update([
            'name' => $request->name,
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Kategória bola úspešne upravená'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table('note_category')->where('category_id', $id)->delete();
        DB::table('categories')->where('id', $id)->delete();

        return response()->json(['message' => 'Kategória bola úspešne zmazaná'], Response::HTTP_OK);
    }
}
