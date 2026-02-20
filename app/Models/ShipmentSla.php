<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentSla extends Model
{
    use HasFactory;

    protected $table = 'shipment_sla';

    protected $fillable = [
        'shipment_id',
        'sla_id',
        'deadline_at',
        'status',
        'actual_delivered_at',
        'hours_difference',
    ];

    protected $casts = [
        'deadline_at' => 'datetime',
        'actual_delivered_at' => 'datetime',
        'hours_difference' => 'integer',
    ];

    /**
     * Get shipment
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Get SLA definition
     */
    public function slaDefinition(): BelongsTo
    {
        return $this->belongsTo(SlaDefinition::class, 'sla_id');
    }

    /**
     * Check if SLA is on time
     */
    public function isOnTime(): bool
    {
        return $this->status === 'on_time';
    }

    /**
     * Check if SLA is late
     */
    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    /**
     * Check if SLA is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
