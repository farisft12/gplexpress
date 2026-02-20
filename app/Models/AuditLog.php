<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'event_category',
        'user_id',
        'ip_address',
        'user_agent',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'description',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an audit event
     */
    public static function log(
        string $eventType,
        string $eventCategory = 'general',
        ?int $userId = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'event_type' => $eventType,
            'event_category' => $eventCategory,
            'user_id' => $userId ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Scope: Security events
     */
    public function scopeSecurity($query)
    {
        return $query->where('event_category', 'security');
    }

    /**
     * Scope: Financial events
     */
    public function scopeFinancial($query)
    {
        return $query->where('event_category', 'financial');
    }

    /**
     * Scope: System events
     */
    public function scopeSystem($query)
    {
        return $query->where('event_category', 'system');
    }

    /**
     * Scope: Recent events
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
