<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function edit(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|max:255',
            'email' => 'required|unique:users,email,' . $id,
            'current_password' => 'required|max:255',
            'new_password' => 'nullable|max:255',
            'phone_number' => 'nullable|max:100',
        ]);

        try {
            $user = User::findOrFail($id);

            DB::beginTransaction();

            // Validasi password saat ini
            if (!Hash::check($request->input('current_password'), $user->password)) {
                throw new \Exception('Password saat ini tidak valid.');
            }

            // Validasi password baru jika ada
            if ($request->filled('new_password')) {
                $request->validate([
                    'confirmation_password' => 'required|max:255|same:new_password',
                ]);
                $user->password = bcrypt($request->input('new_password')); // Menggunakan 'new_password' untuk update password
            }

            $data = $request->only(['name', 'email', 'phone_number']);
            $user->update($data); // Simpan item ke database

            DB::commit(); // Commit transaksi jika semuanya berhasil
            return response()->json(['data' => $user], 201);
        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaksi jika terjadi kesalahan
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
