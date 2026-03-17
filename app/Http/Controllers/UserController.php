<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::query()->orderByDesc('updated_at')->get();
        return response()->json(['users' => $users], Response::HTTP_OK);
    }

    public function fetchPremiumUsers(){
        $users = User::getPremiumUsers();
        return response()->json(['users' => $users], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return response()->json(['message' => 'Užívateľ bol vytvorený'], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Užívateľ nebol nájdený.']);
        }
        return response()->json(['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Užívateľ neexistuje.']);
        }
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return response()->json(['message' => 'Užívateľ bol úspešne upravený.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Užívateľ neexistuje.'], Response::HTTP_NOT_FOUND);
        }
        $user->delete();
        return response()->json(['message' => 'Užívateľ bol úspešne vymazaný'], Response::HTTP_OK);
    }
}
