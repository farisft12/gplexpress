<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'city',
        'district',
        'postal_code',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get couriers assigned to this zone
     */
    public function couriers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'courier_zones', 'zone_id', 'courier_id')
            ->withTimestamps();
    }

    /**
     * Get shipments in this zone
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Check if zone is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get active zones for branch
     */
    public static function activeForBranch(?int $branchId = null)
    {
        $query = self::where('status', 'active');
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        return $query;
    }

    /**
     * Find zone by address (city, district, postal_code)
     */
    public static function findByAddress(?string $city = null, ?string $district = null, ?string $postalCode = null, ?int $branchId = null)
    {
        $query = self::where('status', 'active');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Match by city first
        if ($city) {
            $query->where(function ($q) use ($city) {
                $q->where('city', 'ILIKE', "%{$city}%")
                  ->orWhereNull('city');
            });
        }

        // Then by district
        if ($district) {
            $query->where(function ($q) use ($district) {
                $q->where('district', 'ILIKE', "%{$district}%")
                  ->orWhereNull('district');
            });
        }

        // Finally by postal code (most specific)
        if ($postalCode) {
            $query->where(function ($q) use ($postalCode) {
                $q->where('postal_code', $postalCode)
                  ->orWhereNull('postal_code');
            });
        }

        return $query->first();
    }
}
