<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Concert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConcertController extends Controller
{
    public function index()
    {
        $concert = Concert::select('id', 'name', 'date', 'location', 'vendors_id', 'poster')->get();
        return response(['data' => $concert]);
    }


    public function store(Request $request)
    {
        DB::beginTransaction(); // Memulai transaksi

        try {
            $request->validate([
                'name' => 'required|max:100',
                'date' => 'required|date',
                'location' => 'required|max:100',
                'vendors_id' => 'required|integer',
                'poster_file' => 'nullable|mimes:jpg,png',
            ]);

            $concert = Concert::create($request->except('poster_file'));

            if ($request->hasFile('poster_file')) {
                $file = $request->file('poster_file');
                $fileName = $file->getClientOriginalName();
                $newName = Carbon::now()->timestamp . '_' . $fileName;

                if (!$concert) {
                    DB::rollback(); // Rollback transaksi jika gagal membuat item
                    return response()->json(['message' => 'Gagal menyimpan data.'], 500);
                }

                Storage::disk('public')->putFileAs('concertPoster', $file, $newName);
                $concert->poster = $newName;
            }

            $concert->save(); // Simpan item ke database

            DB::commit(); // Commit transaksi jika semuanya berhasil
            return response()->json(['data' => $concert], 201);
        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaksi jika terjadi kesalahan
            return response()->json(['message' => 'Gagal menyimpan data.'], 500);
        }
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Mulai transaksi

        try {
            $request->validate([
                'name' => 'required|max:100',
                'date' => 'required|date',
                'location' => 'required|max:100',
                'poster_file' => 'nullable|mimes:jpg,png',
            ]);

            $concert = Concert::findOrFail($id);

            // Jika ada file poster baru yang diunggah
            if ($request->file('poster_file')) {
                $file = $request->file('poster_file');
                $fileName = $file->getClientOriginalName();
                $newName = Carbon::now()->timestamp . '_' . $fileName;

                // Simpan file baru
                Storage::disk('public')->putFileAs('concertPoster', $file, $newName);

                // Hapus file poster lama jika ada
                if ($concert->poster) {
                    Storage::disk('public')->delete('concertPoster/' . $concert->poster);
                }

                // Update kolom poster dengan nama file yang baru
                $concert->poster = $newName;
            }

            // Update data lainnya
            $concert->update($request->except('poster_file'));

            DB::commit(); // Commit transaksi jika tidak ada kesalahan
            return response(['data' => $concert]);
        } catch (\Exception $e) {
            DB::rollback(); // Rollback transaksi jika terjadi kesalahan
            return response()->json(['message' => 'Gagal memperbarui data.'], 500);
        }
    }
}
