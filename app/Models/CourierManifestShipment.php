<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierManifestShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_id',
        'shipment_id',
    ];

    /**
     * Get manifest
     */
    public function manifest(): BelongsTo
    {
        return $this->belongsTo(CourierManifest::class, 'manifest_id');
    }

    /**
     * Get shipment
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }
}

