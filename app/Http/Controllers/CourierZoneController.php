<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;

class CourierZoneController extends Controller
{
    /**
     * Show form to assign zones to courier
     */
    public function edit(User $courier)
    {
        $this->authorize('update', $courier);
        
        // Verify user is a courier
        if (!$courier->isKurir()) {
            abort(404);
        }

        // Verify courier is from same branch (unless super admin)
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
            if ($courier->branch_id !== auth()->user()->branch_id) {
                abort(403);
            }
        }

        // Get zones for courier's branch
        $branchId = $courier->branch_id ?? auth()->user()->branch_id;
        $zones = Zone::where('branch_id', $branchId)
            ->where('status', 'active')
            ->get();

        // Get currently assigned zones
        $assignedZoneIds = $courier->zones()->pluck('zones.id')->toArray();

        return view('admin.zones.assign-courier', compact('courier', 'zones', 'assignedZoneIds'));
    }

    /**
     * Update courier zone assignments
     */
    public function update(Request $request, User $courier)
    {
        $this->authorize('update', $courier);
        
        // Verify user is a courier
        if (!$courier->isKurir()) {
            abort(404);
        }

        // Verify courier is from same branch (unless super admin)
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
            if ($courier->branch_id !== auth()->user()->branch_id) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['exists:zones,id'],
        ]);

        // Verify all zones are from same branch
        $branchId = $courier->branch_id ?? auth()->user()->branch_id;
        if (!empty($validated['zone_ids'])) {
            $invalidZones = Zone::whereIn('id', $validated['zone_ids'])
                ->where('branch_id', '!=', $branchId)
                ->exists();
            
            if ($invalidZones) {
                return back()->withErrors(['zone_ids' => 'Zone harus dari cabang yang sama dengan kurir.']);
            }
        }

        // Sync zones
        $courier->zones()->sync($validated['zone_ids'] ?? []);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone kurir berhasil diperbarui.');
    }
}
