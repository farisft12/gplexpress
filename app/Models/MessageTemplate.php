<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'channel',
        'content',
        'branch_id',
        'is_active',
        'variables',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    /**
     * Get branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope: Active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Global templates (no branch_id)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('branch_id');
    }

    /**
     * Scope: Branch-specific templates
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id'); // Include global templates
        });
    }

    /**
     * Get template by code and branch
     */
    public static function getTemplate(string $code, ?int $branchId = null): ?self
    {
        $query = self::where('code', $code)
            ->where('is_active', true);

        if ($branchId) {
            // Try branch-specific first, fallback to global
            $template = (clone $query)
                ->where('branch_id', $branchId)
                ->first();

            if ($template) {
                return $template;
            }
        }

        // Fallback to global template
        return (clone $query)
            ->whereNull('branch_id')
            ->first();
    }
}
