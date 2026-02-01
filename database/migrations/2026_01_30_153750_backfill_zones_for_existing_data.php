<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\Zone;
use App\Models\Shipment;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create default zone for each branch
        $branches = Branch::all();
        
        foreach ($branches as $branch) {
            // Check if default zone already exists
            $existingZone = Zone::where('branch_id', $branch->id)
                ->where('name', 'LIKE', '%Default%')
                ->first();
            
            if (!$existingZone) {
                Zone::create([
                    'branch_id' => $branch->id,
                    'name' => 'Default Zone - ' . $branch->name,
                    'description' => 'Default zone untuk cabang ' . $branch->name,
                    'status' => 'active',
                ]);
            }
        }
        
        // Assign existing shipments to default zone of their destination branch
        $shipments = Shipment::whereNull('zone_id')->get();
        
        foreach ($shipments as $shipment) {
            if ($shipment->destination_branch_id) {
                $defaultZone = Zone::where('branch_id', $shipment->destination_branch_id)
                    ->where('status', 'active')
                    ->first();
                
                if ($defaultZone) {
                    $shipment->update(['zone_id' => $defaultZone->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove default zones (optional, can be kept)
        // Zone::where('name', 'LIKE', '%Default Zone%')->delete();
    }
};
