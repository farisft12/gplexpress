<?php

namespace App\Http\Controllers;

use App\Models\Expedition;
use Illuminate\Http\Request;

class ExpeditionController extends Controller
{
    /**
     * Display a listing of expeditions
     */
    public function index(Request $request)
    {
        $query = Expedition::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $expeditions = $query->latest()->paginate(15);

        return view('admin.expeditions.index', compact('expeditions'));
    }

    /**
     * Show the form for creating a new expedition
     */
    public function create()
    {
        return view('admin.expeditions.create');
    }

    /**
     * Store a newly created expedition
     */
    public function store(\App\Http\Requests\StoreExpeditionRequest $request)
    {
        Expedition::create($request->validated());

        return redirect()->route('admin.expeditions.index')
            ->with('success', 'Ekspedisi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the expedition
     */
    public function edit(Expedition $expedition)
    {
        return view('admin.expeditions.edit', compact('expedition'));
    }

    /**
     * Update the specified expedition
     */
    public function update(\App\Http\Requests\UpdateExpeditionRequest $request, Expedition $expedition)
    {
        $expedition->update($request->validated());

        return redirect()->route('admin.expeditions.index')
            ->with('success', 'Ekspedisi berhasil diperbarui.');
    }

    /**
     * Remove the specified expedition
     */
    public function destroy(Expedition $expedition)
    {
        if ($expedition->shipments()->count() > 0) {
            return back()->withErrors(['error' => 'Ekspedisi tidak dapat dihapus karena masih memiliki paket.']);
        }

        $expedition->delete();

        return redirect()->route('admin.expeditions.index')
            ->with('success', 'Ekspedisi berhasil dihapus.');
    }
}
