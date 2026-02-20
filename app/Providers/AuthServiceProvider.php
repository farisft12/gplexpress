<?php

namespace App\Providers;

use App\Models\CourierSettlement;
use App\Models\Zone;
use App\Models\User;
use App\Models\Branch;
use App\Models\PricingTable;
use App\Policies\SettlementPolicy;
use App\Policies\UserPolicy;
use App\Policies\ZonePolicy;
use App\Policies\BranchPolicy;
use App\Policies\PricingTablePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        CourierSettlement::class => SettlementPolicy::class,
        Zone::class => ZonePolicy::class,
        Branch::class => BranchPolicy::class,
        PricingTable::class => PricingTablePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

