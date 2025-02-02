<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'no_wa' => 'required|numeric|regex:/^\d{10,14}$/',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ], [
            'name.required' => 'nama wajib diisi!',
            'name.string' => 'nama harus berupa teks!',
            'email.required' => 'email wajib diisi!',
            'email.email' => 'masukkan email yang benar!',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi!',
            'no_wa.numeric' => 'Nomor WhatsApp harus berupa angka.',
            'no_wa.regex' => 'Nomor WhatsApp harus terdiri dari 10 hingga 14 digit.',
            'password.required' => 'password wajib diisi',
            'confirm_password.required' => 'konfirmasi password wajib diisi!',
            'confirm_password.same' => 'konfirmasi password harus sama dengan password sebelumnya!'
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
            'message' => 'Registrasi sukses',
            'data' => $user
        ], 200);
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'email wajib diisi',
            'email.email' => 'masukkan email yang benar',
            'password.required' => 'password wajib diisi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada kesalahan',
                'data' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');


        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'login berhasil',
                'token' => $token,
                'data' => Auth::user(),
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'token berhasil dihapus'
        ], 200);
    }
}
