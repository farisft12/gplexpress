<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expedition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    /**
     * Get shipments from this expedition
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Scope for active expeditions only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
