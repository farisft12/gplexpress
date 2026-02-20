<?php

namespace App\Http\Controllers;

use App\Models\PricingTable;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingController extends Controller
{
    /**
     * Display a listing of pricing tables
     */
    public function index(Request $request)
    {
        $query = PricingTable::with(['originBranch', 'destinationBranch']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('origin_branch_id')) {
            $query->where('origin_branch_id', $request->origin_branch_id);
        }

        if ($request->filled('destination_branch_id')) {
            $query->where('destination_branch_id', $request->destination_branch_id);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pricings = $query->latest()->paginate(15);
        
        // Cache branches list as it doesn't change frequently
        $branches = cache()->remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });

        return view('admin.pricing.index', compact('pricings', 'branches'));
    }

    /**
     * Show the form for creating a new pricing
     */
    public function create()
    {

        $this->authorize('create', PricingTable::class);
        

        // Cache branches list as it doesn't change frequently
        $branches = cache()->remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });
        return view('admin.pricing.create', compact('branches'));
    }

    /**
     * Store a newly created pricing
     */
    public function store(\App\Http\Requests\StorePricingRequest $request)
    {
        DB::transaction(function () use ($request) {
            PricingTable::create($request->validated());
            // Clear cache if needed
            cache()->forget('active_branches');
        });

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Tarif harga berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a pricing
     */
    public function edit(PricingTable $pricing)
    {

        $this->authorize('update', $pricing);
        

        // Cache branches list as it doesn't change frequently
        $branches = cache()->remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });
        return view('admin.pricing.edit', compact('pricing', 'branches'));
    }

    /**
     * Update the specified pricing
     */
    public function update(\App\Http\Requests\UpdatePricingRequest $request, PricingTable $pricing)
    {
        DB::transaction(function () use ($pricing, $request) {
            $pricing->update($request->validated());
            // Clear cache if needed
            cache()->forget('active_branches');
        });

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Tarif harga berhasil diperbarui.');
    }

    /**
     * Remove the specified pricing
     */
    public function destroy(PricingTable $pricing)
    {

        $this->authorize('delete', $pricing);
        

        DB::transaction(function () use ($pricing) {
            $pricing->delete();
            // Clear cache if needed
            cache()->forget('active_branches');
        });

        return redirect()->route('admin.pricing.index')
            ->with('success', 'Tarif harga berhasil dihapus.');
    }
}







