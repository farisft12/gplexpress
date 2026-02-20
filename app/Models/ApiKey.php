<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'secret',
        'is_active',
        'rate_limit',
        'allowed_ips',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'rate_limit' => 'integer',
    ];

    protected $hidden = [
        'secret',
    ];

    /**
     * Generate a new API key
     */
    public static function generate(): string
    {
        return 'gpl_' . Str::random(40);
    }

    /**
     * Check if key is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is allowed
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true; // No IP restriction
        }

        return in_array($ip, $this->allowed_ips);
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
