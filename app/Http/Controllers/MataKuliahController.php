<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MataKuliahController extends Controller
{
    public function index()
    {
        $mataKuliahs = MataKuliah::paginate(10);

        if ($mataKuliahs->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data mata kuliah',
                'data' => []
            ], 200);
        }

        $mataKuliahs->getCollection()->transform(function ($mk) {
            return [
                'id' => Crypt::encryptString($mk->id),
                'kode_mk' => $mk->kode_mk,
                'nama_mk' => $mk->nama_mk,
                'sks' => $mk->sks,
                'created_at' => $mk->created_at,
                'updated_at' => $mk->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data mata kuliah ditemukan',
            'data' => $mataKuliahs
        ], 200);
    }

    public function select()
    {
        return response()->json(MataKuliah::select('id', 'nama_mk')->get());
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'kode_mk' => 'required|numeric|unique:mata_kuliahs,kode_mk',
                'nama_mk' => 'required|string|max:40',
                'sks' => 'required|numeric'
            ], [
                'kode_mk.required' => 'kode mata kuliah wajib diisi',
                'kode_mk.numeric' => 'kode mata kuliah harus berupa angka',
                'kode_mk.unique' => 'kode sudah digunakan, silahkan masukkan kode yang berbeda',
                'nama_mk.required' => 'nama mata kuliah wajib diisi',
                'nama_mk.string' => 'nama mata kuliah harus berupa teks',
                'nama_mk.max' => 'nama mata kuliah tidak boleh lebih dari 40 karakter',
                'sks.required' => 'sks wajib diisi',
                'sks.numeric' => 'sks harus berupa angka',
            ]);

            $mataKuliah = MataKuliah::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data mata kuliah berhasil disimpan',
                'data' => $mataKuliah
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $decrypt = Crypt::decryptString($id);

        $mataKuliah = MataKuliah::find($decrypt);

        if (!$mataKuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data mata kuliah ditemukan',
            'data' => $mataKuliah
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $decrypt = Crypt::decryptString($id);

        $mataKuliah = MataKuliah::find($decrypt);

        if (!$mataKuliah) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'nama_mk' => 'required|string|max:40',
            'sks' => 'required|numeric'
        ], [
            'nama_mk.required' => 'nama mata kuliah wajib diisi',
            'nama_mk.string' => 'nama mata kuliah harus berupa teks',
            'nama_mk.max' => 'nama mata kuliah tidak boleh lebih dari 40 karakter',
            'sks.required' => 'sks wajib diisi',
            'sks.numeric' => 'sks harus berupa angka',
        ]);

        $mataKuliah->kode_mk = $request->kode_mk;
        $mataKuliah->nama_mk = $request->nama_mk;
        $mataKuliah->sks = $request->sks;
        $mataKuliah->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data',
            'data' => $mataKuliah
        ], 200);
    }

    public function destroy($id)
    {
        try {
            $decrypt = Crypt::decryptString($id);

            $mataKuliah = MataKuliah::find($decrypt);

            if (!$mataKuliah) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            $mataKuliah->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data mata kuliah berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
