<?php

namespace App\Providers;

use App\Models\CourierSettlement;
use App\Models\Zone;
use App\Models\User;
use App\Models\Branch;
<<<<<<< HEAD
use App\Models\PricingTable;
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
use App\Policies\SettlementPolicy;
use App\Policies\UserPolicy;
use App\Policies\ZonePolicy;
use App\Policies\BranchPolicy;
<<<<<<< HEAD
use App\Policies\PricingTablePolicy;
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
        PricingTable::class => PricingTablePolicy::class,
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

