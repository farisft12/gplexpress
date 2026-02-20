<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\BranchScope;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'resi_number',
        'source_type',
        'expedition_id',
        'external_resi_number',
        'branch_id',
        'origin_branch_id',
        'destination_branch_id',
        'zone_id',
        'package_type',
        'weight',
        'type',
        'cod_amount',
        'cod_shipping_cost',
        'cod_admin_fee',
        'shipping_cost',
        'payment_method',
        'payment_transaction_id',
        'payment_status',
        'cod_status',
        'sender_name',
        'sender_phone',
        'sender_address',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'status',
        'courier_id',
        'assigned_at',
        'out_for_delivery_at',
        'eta_at',
        'delivered_at',
        'failed_at',
        'delivery_notes',
        'cod_collected_by',
        'cod_collected_at',
        'cod_payment_received_at',
        'cod_collection_notes',
        'destination_courier_id',
        'destination_courier_assigned_at',
        'destination_courier_out_for_delivery_at',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new BranchScope);
    }

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'cod_shipping_cost' => 'decimal:2',
        'cod_admin_fee' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'weight' => 'decimal:2',
        'assigned_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'eta_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'cod_collected_at' => 'datetime',
        'cod_payment_received_at' => 'datetime',
        'destination_courier_assigned_at' => 'datetime',
        'destination_courier_out_for_delivery_at' => 'datetime',
    ];

    /**
     * Get total COD amount to collect (Nominal + Ongkir + Admin)
     * Backward compatible: old COD has null cod_shipping_cost/cod_admin_fee, so total = cod_amount
     */
    protected function totalCodCollectible(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => (float) $this->cod_amount
                + (float) ($this->cod_shipping_cost ?? 0)
                + (float) ($this->cod_admin_fee ?? 0),
        );
    }

    /**
     * Generate unique resi number
     */
    public static function generateResiNumber(): string
    {
        do {
            $resi = 'GPL' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        } while (self::where('resi_number', $resi)->exists());

        return $resi;
    }

    /**
     * Get branch (operational branch)
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get origin branch
     */
    public function originBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    /**
     * Get destination branch
     */
    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    /**
     * Get expedition (for ekspedisi_lain source type)
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Get courier
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    /**
     * Get status histories
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ShipmentStatusHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get manifest shipments
     */
    public function manifestShipments(): HasMany
    {
        return $this->hasMany(CourierManifestShipment::class, 'shipment_id');
    }

    /**
     * Get payment transactions
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Get shipment SLA
     */
    public function shipmentSla(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ShipmentSla::class);
    }

    /**
     * Get zone
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get destination courier (kurir tujuan yang mengantar dan menagih COD)
     */
    public function destinationCourier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destination_courier_id');
    }

    /**
     * Get COD collector (kurir yang menagih COD)
     */
    public function codCollector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_collected_by');
    }

    /**
     * Check if can update status
     */
    public function canUpdateStatus(string $newStatus): bool
    {
        // Status flow rules
        if ($newStatus === 'diproses') {
            return in_array($this->status, ['pickup']);
        }
        
        if ($newStatus === 'dalam_pengiriman') {
            return in_array($this->status, ['diproses']);
        }
        
        if ($newStatus === 'sampai_di_cabang_tujuan') {
            return in_array($this->status, ['dalam_pengiriman']);
        }
        
        if ($newStatus === 'diterima') {
            return in_array($this->status, ['sampai_di_cabang_tujuan']);
        }
        
        if ($newStatus === 'cod_lunas') {
            // COD can only be marked as paid when package has reached destination branch
            return $this->isCOD() &&
                   $this->status === 'sampai_di_cabang_tujuan' &&
                   $this->cod_status === 'belum_lunas';
        }

        return false;
    }

    /**
     * Check if is COD
     */
    public function isCOD(): bool
    {
        return $this->type === 'cod';
    }

    /**
     * Get reviews for this shipment
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if shipment can be assigned for COD collection
     */
    public function canBeAssignedForCodCollection(): bool
    {
        return $this->status === 'sampai_di_cabang_tujuan' 
            && $this->type === 'cod' 
            && $this->cod_status === 'belum_lunas'
            && (is_null($this->cod_collected_by) || is_null($this->destination_courier_id));
    }

    /**
     * Check if shipment can be assigned to destination courier
     */
    public function canBeAssignedToDestinationCourier(): bool
    {
        return $this->status === 'sampai_di_cabang_tujuan' 
            && is_null($this->destination_courier_id);
    }
}

