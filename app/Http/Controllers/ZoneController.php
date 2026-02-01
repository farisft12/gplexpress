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
        
        // Get branches for filter (super admin only)
        $branches = auth()->user()->isSuperAdmin() ? Branch::all() : collect();

        return view('admin.zones.index', compact('zones', 'branches'));
    }

    /**
     * Show the form for creating a new zone
     */
    public function create()
    {
        $this->authorize('create', Zone::class);
        
        // Get branches (super admin sees all, others see only their branch)
        if (auth()->user()->isSuperAdmin()) {
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
    public function store(Request $request)
    {
        $this->authorize('create', Zone::class);
        
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Verify branch access (unless super admin)
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id != $validated['branch_id']) {
            return back()->withErrors(['branch_id' => 'Anda tidak memiliki akses ke cabang ini.']);
        }

        Zone::create($validated);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone berhasil dibuat.');
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
        if (auth()->user()->isSuperAdmin()) {
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
    public function update(Request $request, Zone $zone)
    {
        $this->authorize('update', $zone);
        
        $validated = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        // Verify branch access (unless super admin)
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id != $validated['branch_id']) {
            return back()->withErrors(['branch_id' => 'Anda tidak memiliki akses ke cabang ini.']);
        }

        $zone->update($validated);

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone berhasil diperbarui.');
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
