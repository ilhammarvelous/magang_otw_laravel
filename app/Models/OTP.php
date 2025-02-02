<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;

    protected $table = 'otps';
    protected $fillable = [
        'nomor_wa',
        'otp_code',
        'expires_at'
    ];

    public function isExpired()
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }
}
