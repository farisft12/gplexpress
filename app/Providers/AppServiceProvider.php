<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\CourierSettlement;
use App\Models\Zone;
use App\Policies\ShipmentPolicy;
use App\Policies\BranchPolicy;
use App\Policies\SettlementPolicy;
use App\Policies\ZonePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Shipment::class => ShipmentPolicy::class,
        Branch::class => BranchPolicy::class,
        CourierSettlement::class => SettlementPolicy::class,
        Zone::class => ZonePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
