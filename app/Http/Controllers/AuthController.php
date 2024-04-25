<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        unset($user->email_verified_at);
        unset($user->phone_number);
        unset($user->created_at);
        unset($user->updated_at);
        unset($user->deleted_at);

        $user->tokens()->delete();
        $token = $user->createToken('ini token login')->plainTextToken;
        $user->token = $token;

        return response(['data' => $user]);
    }

    public function logout()
    {
        $user = auth()->user();
        $user->tokens()->delete();

        return response(['message' => 'logout berhasil']);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email',
            'password' => 'required|max:255',
            'phone_number' => 'nullable|max:100',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only(['name', 'email', 'password', 'phone_number']);
            $data['roles_id'] = 1;
            // Enkripsi password sebelum disimpan
            $data['password'] = bcrypt($request->input('password'));

            $user = User::create($data);

            $user->save();
            DB::commit();
            return response()->json(['data' => $user], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500); // Mengembalikan pesan kesalahan
        }
    }

    public function me()
    {
        return response(['data' => auth()->user()]);
    }
}
