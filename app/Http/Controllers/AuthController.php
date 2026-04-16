<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;
use Throwable;

class AuthController extends Controller
{

    public function destroyProfilePhoto(Request $request)
    {
        $attachment = $request->user()->profilePhoto;

        if (!$attachment) {
            return response()->json([
                'message' => 'Profilová fotka neexistuje.',
            ], Response::HTTP_NOT_FOUND);
        }

        DB::transaction(function () use ($attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
            $attachment->delete();
        });

        return response()->json([
            'message' => 'Profilová fotka bola odstránená.',
        ], Response::HTTP_OK);
    }

    public function storeProfilePhoto(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', File::image()->max('3mb')],
        ]);

        $user = $request->user();
        $file = $validated['file'];

        $disk = 'public';
        $directory = 'profile_photos/users/' . $user->id;

        $path = null;

        try {
            DB::beginTransaction();

            $oldProfilePhoto = $user->profilePhoto;

            $path = $file->store($directory, $disk);

            $newPhoto = $user->profilePhoto()->create([
                'public_id' => (string) Str::ulid(),
                'collection' => 'profile_photo',
                'visibility' => 'public',
                'disk' => $disk,
                'path' => $path,
                'stored_name' => basename($path),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            if ($oldProfilePhoto) {
                Storage::disk($oldProfilePhoto->disk)->delete($oldProfilePhoto->path);
                $oldProfilePhoto->delete();
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();

            if ($path) {
                Storage::disk($disk)->delete($path);
            }

            return response()->json([
                'message' => 'Profilovú fotku sa nepodarilo uložiť.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json([
            'message' => 'Profilová fotka bola uložená.',
            'profile_photo' => $newPhoto,
            'profile_photo_url' => $newPhoto->publicUrl(),
        ], Response::HTTP_CREATED);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:128'],
            'last_name'  => ['required', 'string', 'min:2', 'max:128'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => $validated['password'],
            'role'       => 'user',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registrácia prebehla úspešne.',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Nesprávny email alebo heslo.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Prihlásenie bolo úspešné.',
            'user' => $user,
            'token' => $token,
        ], Response::HTTP_OK);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'active_sessions' => $request->user()->tokens()->count(),
        ], Response::HTTP_OK);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Používateľ bol odhlásený z aktuálneho zariadenia.',
        ], Response::HTTP_OK);
    }

    public function logoutAll(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'message' => 'Užívateľ bol odhlásený zo všetkých zariadení.'
        ], Response::HTTP_OK);
    }

    public function changePassword(Request $request){
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' =>  ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()->symbols()]
        ]);

        $request->user()->update(
            ['password' => $validated['password']]
        );

        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Heslo bolo úspešne zmenené.'
        ], Response::HTTP_OK);
    }

    public function editUser(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:128'],
            'last_name'  => ['required', 'string', 'min:2', 'max:128'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:8192']
        ]);

        $user = $request->user();

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            $path = $file->store('profile_pictures', 'public');
            $validated['image'] = $path;
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Vaše údaje boli úspešne zmenené',
        ], Response::HTTP_OK);
    }

    public function changeProfilePicture(Request $request){
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:8192']
        ]);

        if(!$request->hasFile('image')){
            return response()->json(['message' => 'Vaša profilová fotka nebola zmenená'], Response::HTTP_OK);
        }
        $user = $request->user();
        if($user->image){
            Storage::disk('public')->delete($user->image);
        }

        $file = $request->file('image');

        $path = $file->store('profile_pictures', 'public');

        $user->update(['image' => $path]);

        return response()->json([
            'message' => 'Vaša profilová fotka bola úspešne upravená.'
        ], Response::HTTP_OK);
    }
}
