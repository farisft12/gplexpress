<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourierManifest extends Model
{
    use HasFactory;

    protected $fillable = [
        'courier_id',
        'manifest_date',
        'status',
        'total_packages',
        'delivered_count',
        'failed_count',
    ];

    protected $casts = [
        'manifest_date' => 'date',
    ];

    /**
     * Get courier
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get manifest shipments
     */
    public function manifestShipments(): HasMany
    {
        return $this->hasMany(CourierManifestShipment::class, 'manifest_id');
    }

    /**
     * Get shipments through pivot
     */
    public function shipments()
    {
        return $this->belongsToMany(Shipment::class, 'courier_manifest_shipments', 'manifest_id', 'shipment_id')
                    ->withTimestamps();
    }

    /**
     * Update counts
     */
    public function updateCounts(): void
    {
        $this->delivered_count = $this->shipments()->where('status', 'terkirim')->count();
        $this->failed_count = $this->shipments()->where('status', 'gagal')->count();
        $this->save();
    }
}

