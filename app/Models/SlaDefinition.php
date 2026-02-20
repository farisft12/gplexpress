<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'deadline_hours',
        'description',
        'is_active',
    ];

    protected $casts = [
        'deadline_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get shipment SLAs
     */
    public function shipmentSlas(): HasMany
    {
        return $this->hasMany(ShipmentSla::class, 'sla_id');
    }

    /**
     * Get active SLA definitions
     */
    public static function active()
    {
        return self::where('is_active', true);
    }

    /**
     * Get SLA by code
     */
    public static function findByCode(string $code)
    {
        return self::where('code', $code)->where('is_active', true)->first();
    }
}
