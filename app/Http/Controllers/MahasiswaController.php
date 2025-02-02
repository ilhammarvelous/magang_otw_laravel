<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mahasiswas = Mahasiswa::paginate(5);

        if ($mahasiswas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data mahasiswa',
                'data' => []
            ], 200);
        }

        $mahasiswas->getCollection()->transform(function ($mhs) {
            return [
                'id' => Crypt::encryptString($mhs->id), // 🔐 Enkripsi ID
                'nim' => $mhs->nim,
                'nama' => $mhs->nama,
                'no_hp' => $mhs->no_hp,
                'agama' => $mhs->agama,
                'prodi' => $mhs->prodi,
                'status' => $mhs->status,
                'created_at' => $mhs->created_at,
                'updated_at' => $mhs->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data mahasiswa ditemukan',
            'data' => $mahasiswas
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData =  $request->validate([
                'nim' => 'required|unique:mahasiswas,nim|max:10',
                'nama' => 'required|string|max:50',
                'no_hp' => 'required|digits_between:10,13',
                'prodi' => 'required|string|max:30|in:Teknik Informatika,Teknik Sipil,Teknik Arsitektur,Teknik Industri,Teknik Elektro,Teknik Mesin',
                'agama' => 'required|in:Islam,Protestan,Katholik,Budha,Hindu,Konghucu',
                'status' => 'required'
            ], [
                'nim.required' => 'nim wajib diisi!',
                'nim.unique' => 'nim sudah digunakan, silahkan masukkan nim yang berbeda',
                'nim.max' => 'nim tidak boleh lebih dari 10 karakter !!',
                'nama.required' => 'nama wajib diisi!',
                'nama.string' => 'nama harus berupa teks!',
                'nama.max' => 'nama tidak boleh lebih dari 50 karakter',
                'no_hp.required' => 'no hp wajib diisi!',
                'no_hp.digits_between' => 'no hp harus minimal 10 digit atau maksimal 13 digit !!',
                'prodi.required' => 'prodi wajib diisi!',
                'prodi.string' => 'nama prodi harus berupa teks !',
                'prodi.in' => 'Prodi harus salah satu dari : Teknik Informatika, Teknik Sipil, Teknik Arsitektur, Teknik Industri, Teknik Elektro, Teknik Mesin.',
                'prodi.max' => 'nama prodi tidak boleh lebih dari 30 karakter!',
                'agama.required' => 'agama wajib diisi!',
                'agama.in' => 'Agama harus salah satu dari : Islam, Protestan, Katholik, Hindu, Buddha, atau Konghucu.',
                'status.required' => 'Status wajib diisi !',
            ]);

            $mahasiswa = Mahasiswa::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan',
                'data' => $mahasiswa
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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $decrypt = Crypt::decryptString($id);

        $mahasiswa = Mahasiswa::find($decrypt);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data mahasiswa ditemukan',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $decrypt = Crypt::decryptString($id);

        $mahasiswa = Mahasiswa::find($decrypt);

        if (!$mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'nama' => 'required|string|max:50',
            'no_hp' => 'required|digits_between:10,13',
            'prodi' => 'required|string|max:30|in:Teknik Informatika,Teknik Sipil,Teknik Arsitektur,Teknik Industri,Teknik Elektro,Teknik Mesin',
            'agama' => 'required|in:Islam,Protestan,Katholik,Budha,Hindu,Konghucu',
            'status' => 'required'
        ], [
            'nama.required' => 'nama wajib diisi!',
            'nama.string' => 'nama harus berupa teks!',
            'nama.max' => 'nama tidak boleh lebih dari 50 karakter',
            'no_hp.required' => 'no hp wajib diisi!',
            'no_hp.digits_between' => 'no hp harus minimal 10 digit atau maksimal 13 digit !!',
            'prodi.required' => 'prodi wajib diisi!',
            'prodi.string' => 'nama prodi harus berupa teks !',
            'prodi.max' => 'nama prodi tidak boleh lebih dari 30 karakter!',
            'prodi.in' => 'Prodi harus salah satu dari : Teknik Informatika, Teknik Sipil, Teknik Arsitektur, Teknik Industri, Teknik Elektro, Teknik Mesin.',
            'agama.required' => 'agama wajib diisi!',
            'agama.in' => 'Agama harus salah satu dari : Islam, Protestan, Katolik, Hindu, Buddha, atau Konghucu.',
            'status.required' => 'Status wajib diisi !',
        ]);

        $mahasiswa->nim = $request->nim;
        $mahasiswa->nama = $request->nama;
        $mahasiswa->no_hp = $request->no_hp;
        $mahasiswa->prodi = $request->prodi;
        $mahasiswa->agama = $request->agama;
        $mahasiswa->status = $request->status;
        $mahasiswa->save();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data',
            'data' => $mahasiswa
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $decrypt = Crypt::decryptString($id);

            $mahasiswa = Mahasiswa::find($decrypt);


            if (!$mahasiswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            // Hapus data
            $mahasiswa->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data mahasiswa berhasil dihapus.',
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
