<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'branch_id',
        'avatar',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is owner
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is kurir
     */
    public function isKurir(): bool
    {
        return $this->role === 'kurir';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user can access all branches
     */
    public function canAccessAllBranches(): bool
    {
        return $this->role === 'owner';
    }

    // Legacy methods for backward compatibility (will be removed later)
    public function isSuperAdmin(): bool { return $this->isOwner(); }
    public function isManagerCabang(): bool { return $this->isManager(); }
    public function isAdminCabang(): bool { return $this->isAdmin(); }
    public function isCourierCabang(): bool { return $this->isKurir(); }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get user's branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get current balance
     */
    public function currentBalance()
    {
        return $this->hasOne(CourierCurrentBalance::class, 'courier_id');
    }

    /**
     * Get zones assigned to this courier
     */
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'courier_zones', 'courier_id', 'zone_id')
            ->withTimestamps();
    }
}
