<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhoneVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'code',
        'is_verified',
        'expires_at',
        'verified_at',
        'ip_address',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate a 6-digit verification code
     */
    public static function generateCode(): string
    {
        return str_pad((string) rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new verification record
     */
    public static function createVerification(string $phone, string $ipAddress = null): self
    {
        // Invalidate any existing unverified codes for this phone
        self::where('phone', $phone)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->update(['is_verified' => true]); // Mark as used

        return self::create([
            'phone' => $phone,
            'code' => self::generateCode(),
            'is_verified' => false,
            'expires_at' => now()->addMinutes(10), // 10 minutes expiry
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Verify a code
     */
    public static function verify(string $phone, string $code): bool
    {
        $verification = self::where('phone', $phone)
            ->where('code', $code)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return false;
        }

        $verification->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Check if phone is verified (has at least one verified record)
     */
    public static function isPhoneVerified(string $phone): bool
    {
        return self::where('phone', $phone)
            ->where('is_verified', true)
            ->exists();
    }

    /**
     * Clean up expired verifications (can be run via scheduled task)
     */
    public static function cleanupExpired(): int
    {
        return self::where('expires_at', '<', now())
            ->where('is_verified', false)
            ->delete();
    }
}
