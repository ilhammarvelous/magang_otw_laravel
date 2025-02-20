<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada data pengguna',
                'data' => []
            ], 200);
        }

        $transformedUsers = $users->getCollection()->map(function ($user) {
            return [
                'id' => Crypt::encryptString($user->id),
                'name' => $user->name,
                'email' => $user->email,
                'no_wa' => $user->no_wa,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        $users->setCollection($transformedUsers);

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna ditemukan',
            'data' => $users
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'no_wa' => 'required|numeric|regex:/^\d{10,14}$/',
            'password' => 'required',
        ], [
            'name.required' => 'Nama wajib diisi!',
            'name.string' => 'Nama harus berupa teks!',
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Masukkan email yang benar!',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi!',
            'no_wa.numeric' => 'Nomor WhatsApp harus berupa angka.',
            'no_wa.regex' => 'Nomor WhatsApp harus terdiri dari 10 hingga 14 digit.',
            'password.required' => 'Password wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada kesalahan',
                'data' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_wa' => $request->no_wa,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User baru berhasil ditambahkan',
            'data' => $user
        ], 200);
    }

    public function show($id)
    {
        $decrypt = Crypt::decryptString($id);

        $user = User::find($decrypt);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna ditemukan',
            'data' => $user
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $decrypt = Crypt::decryptString($id);

        $user = User::find($decrypt);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengguna tidak ditemukan',
            ], 404);
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'no_wa' => 'required|numeric|regex:/^\d{10,14}$/',
        ],[
            'name.required' => 'Nama wajib diisi!',
            'name.string' => 'Nama harus berupa teks!',
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Masukkan email yang benar!',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi!',
            'no_wa.numeric' => 'Nomor WhatsApp harus berupa angka.',
            'no_wa.regex' => 'Nomor WhatsApp harus terdiri dari 10 hingga 14 digit.',
        ]);

        if($request->password == ""){
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'no_wa' => $request->no_wa,
            ]);
        } else {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'no_wa' => $request->no_wa,
                'password' => Hash::make($request->password),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengupdate data pengguna',
            'data' => $user
        ], 200);
    }

    public function destroy($id)
    {
        // try {

        //     try {
        //         $decrypt = Crypt::decryptString($id);
        //     } catch (\Exception $e) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'ID tidak valid.',
        //         ], 400);
        //     }
    
        //     if (auth()->id() === (int) $decrypt) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Tidak bisa menghapus akun anda sendiri.'
        //         ], 403);
        //     }
    
        //     $user = User::find($decrypt);
    
        //     if (!$user) {
        //         return response()->json([
        //             'success' => false,
        //             'message' => 'Data pengguna tidak ditemukan.',
        //         ], 404);
        //     }
    
        //     $user->delete();
    
        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Data pengguna berhasil dihapus.',
        //     ], 200);
    
        // } catch (\Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Terjadi kesalahan.',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }

        $authUser = auth()->user();

        $decrypt = Crypt::decryptString($id);

        $user = User::find($decrypt);

        if ($authUser->id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus akun Anda sendiri.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.'
        ], 200);
    }
}
