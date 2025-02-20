<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class MahasiswaMataKuliahController extends Controller
{
    public function index()
    {
        $data = DB::table('mahasiswa_mata_kuliah')
            ->join('mahasiswas', 'mahasiswa_mata_kuliah.mahasiswa_id', '=', 'mahasiswas.id')
            ->join('mata_kuliahs', 'mahasiswa_mata_kuliah.mata_kuliah_id', '=', 'mata_kuliahs.id')
            ->select(
                'mahasiswa_mata_kuliah.id',
                'mahasiswas.nama as mahasiswa',
                'mata_kuliahs.nama_mk as mata_kuliah',
                'mahasiswa_mata_kuliah.semester',
            )
            ->orderBy('mahasiswas.nama')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Data ditemukan',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'mata_kuliah_id' => 'required|array',
            'mata_kuliah_id.*' => 'exists:mata_kuliahs,id',
            'semester' => 'required',
        ], [
            'mahasiswa_id.required' => 'Mahasiswa wajib diisi!',
            'mata_kuliah_id.required' => 'Mata kuliah wajib diisi!',
            'semester.required' => 'Semester wajib diisi!',
        ]);


        foreach ($validatedData['mata_kuliah_id'] as $mkId) {
            DB::table('mahasiswa_mata_kuliah')->insert([
                'mahasiswa_id' => $validatedData['mahasiswa_id'],
                'mata_kuliah_id' => $mkId,
                'semester' => $validatedData['semester'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mata kuliah berhasil ditambahkan ke mahasiswa'
        ], 201);
    }

    public function show($id)
    {
        $data = DB::table('mahasiswa_mata_kuliah')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'mahasiswa_mata_kuliah.mahasiswa_id')
            ->join('mata_kuliahs', 'mata_kuliahs.id', '=', 'mahasiswa_mata_kuliah.mata_kuliah_id')
            ->select(
                'mahasiswa_mata_kuliah.id',
                'mahasiswas.nama as mahasiswa',
                'mata_kuliahs.id as mata_kuliah_id',
                'mata_kuliahs.nama_mk as mata_kuliah',
                'mahasiswa_mata_kuliah.semester'
            )
            ->where('mahasiswa_mata_kuliah.id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan atau belum mengambil mata kuliah.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $updateData = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'semester' => 'required',
        ],[
            'mata_kuliah_id.required' => 'Mata kuliah wajib diisi!',
            'semester.required' => 'Semester wajib diisi!',
        ]);

        $exists = DB::table('mahasiswa_mata_kuliah')->where('id', $id)->exists();

        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan!'
            ], 404);
        }

        DB::table('mahasiswa_mata_kuliah')
            ->where('id', $id)
            ->update([
                'mata_kuliah_id' => $updateData['mata_kuliah_id'],
                'semester' => $updateData['semester'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diperbarui',
            'data' => $updateData
        ], 200);
    }

    public function destroy($id)
    {
        $data = DB::table('mahasiswa_mata_kuliah')->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan!'
            ], 404);
        }

        DB::table('mahasiswa_mata_kuliah')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!'
        ], 200);
    }
}
