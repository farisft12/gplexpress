<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    /**
     * Display a listing of zones
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Zone::class);
        
        $user = auth()->user();
        $query = Zone::with('branch')->latest();

        // Filter by branch (unless owner)
        if (!$user->isOwner() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->isOwner() && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $zones = $query->paginate(20);
        
<<<<<<< HEAD
        // Get branches for filter (owner only)
        $branches = auth()->user()->isOwner() ? Branch::all() : collect();
=======
        // Get branches for filter (super admin only)
        $branches = auth()->user()->isSuperAdmin() ? Branch::all() : collect();
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573

        return view('admin.zones.index', compact('zones', 'branches'));
    }

    /**
     * Show the form for creating a new zone
     */
    public function create()
    {
        $this->authorize('create', Zone::class);
        
<<<<<<< HEAD
        // Get branches (owner sees all, others see only their branch)
        if (auth()->user()->isOwner()) {
=======
        // Get branches (super admin sees all, others see only their branch)
        if (auth()->user()->isSuperAdmin()) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            $branches = Branch::where('status', 'active')->get();
        } else {
            $branches = Branch::where('id', auth()->user()->branch_id)
                ->where('status', 'active')
                ->get();
        }

        return view('admin.zones.create', compact('branches'));
    }

    /**
     * Store a newly created zone
     */
    public function store(\App\Http\Requests\StoreZoneRequest $request)
    {
        try {
            Zone::create($request->validated());

            return redirect()->route('admin.zones.index')
                ->with('success', 'Zone berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified zone
     */
    public function show(Zone $zone)
    {
        $this->authorize('view', $zone);
        
        $zone->load(['branch', 'couriers', 'shipments']);
        
        return view('admin.zones.show', compact('zone'));
    }

    /**
     * Show the form for editing the specified zone
     */
    public function edit(Zone $zone)
    {
        $this->authorize('update', $zone);
        
        // Get branches
<<<<<<< HEAD
        if (auth()->user()->isOwner()) {
=======
        if (auth()->user()->isSuperAdmin()) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            $branches = Branch::where('status', 'active')->get();
        } else {
            $branches = Branch::where('id', auth()->user()->branch_id)
                ->where('status', 'active')
                ->get();
        }

        return view('admin.zones.edit', compact('zone', 'branches'));
    }

    /**
     * Update the specified zone
     */
    public function update(\App\Http\Requests\UpdateZoneRequest $request, Zone $zone)
    {
        try {
            $zone->update($request->validated());

            return redirect()->route('admin.zones.index')
                ->with('success', 'Zone berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified zone
     */
    public function destroy(Zone $zone)
    {
        $this->authorize('delete', $zone);
        
        // Check if zone has shipments
        if ($zone->shipments()->count() > 0) {
            return back()->withErrors(['error' => 'Zone tidak dapat dihapus karena masih memiliki paket.']);
        }

        $zone->delete();

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone berhasil dihapus.');
    }
}
