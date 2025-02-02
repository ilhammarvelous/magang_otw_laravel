<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OTPController extends Controller
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = "https://owa.gusaha.com/api/send-message";
        $this->apiKey = env('WHATSAPP_API_KEY');
    }

    public function generateOTP(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna belum terautentikasi. Silakan login terlebih dahulu.'
            ], 401);
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.'
            ], 400);
        }

        if (!$user->no_wa) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp pengguna tidak terdaftar.'
            ], 400);
        }

        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(1);

        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => $expiresAt,
        ]);

        $message = "Kode OTP Anda adalah: {$otpCode}. Berlaku selama 1 menit.";

        try {
            $response = Http::withHeaders([
                "Accept" => "*/*",
                "Content-Type" => "application/json"
            ])->post($this->apiUrl, [
                    "api_key" => $this->apiKey,
                    "receiver" => $user->no_wa,
                    "data" => [
                        "success" => true,
                        "message" => $message
                    ]
                ]);

            if ($response->failed()) {
                throw new Exception('Gagal mengirim OTP ke WhatsApp.');
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP dikirim ke WhatsApp.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifikasiOTP(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|min:6|max:6',
        ]);

        $otp = User::where('otp_code', $request->otp_code)->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP tidak ditemukan.'
            ], 400);
        }

        if ($otp->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP sudah kadaluwarsa.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil diverifikasi.'
        ], 200);
    }


    public function kirimUlangOTP(Request $request)
    {
        $user = $request->user();

        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna belum terautentikasi. Silakan login terlebih dahulu.'
            ], 401);
        }

        if (!$user->no_wa) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor WhatsApp pengguna tidak terdaftar.'
            ], 400);
        }

        if ($user->otp_code && !$user->isExpired()) {
            $otpCode = $user->otp_code;
        } else {
            $otpCode = rand(100000, 999999);
            $expiresAt = Carbon::now()->addMinutes(2);

            $user->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
            ]);
        }

        $message = "Kode OTP Anda adalah: {$otpCode}. Berlaku selama 1 menit.";

        $response = Http::withHeaders([
            "Accept" => "*/*",
            "Content-Type" => "application/json"
        ])->post($this->apiUrl, [
            "api_key" => $this->apiKey,
            "receiver" => $user->no_wa,
            "data" => [
                "message" => $message
            ]
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim OTP ke WhatsApp.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP dikirim ulang ke WhatsApp.'
        ], 200);
    }
}
