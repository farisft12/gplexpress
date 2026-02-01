<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'address',
        'phone',
        'email',
        'manager_id',
        'status',
    ];

    /**
     * Generate unique branch code
     */
    public static function generateCode(): string
    {
        do {
            $code = 'CAB-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get manager
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get users in this branch
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get admins in this branch
     */
    public function admins()
    {
        return $this->users()->where('role', 'admin');
    }

    /**
     * Get kurirs in this branch
     */
    public function kurirs()
    {
        return $this->users()->where('role', 'kurir');
    }

    /**
     * Get origin pricing
     */
    public function originPricing(): HasMany
    {
        return $this->hasMany(PricingTable::class, 'origin_branch_id');
    }

    /**
     * Get destination pricing
     */
    public function destinationPricing(): HasMany
    {
        return $this->hasMany(PricingTable::class, 'destination_branch_id');
    }

    /**
     * Check if branch is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

