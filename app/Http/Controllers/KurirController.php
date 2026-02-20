<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KurirController extends Controller
{
    /**
     * Display a listing of kurirs
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Filter by role: kurir
        $query = User::where('role', 'kurir')->with('branch');

        // Branch scope for manager (must see only their branch)
        if ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $user->isOwner()) {
            $query->where('branch_id', $request->branch_id);
        }

        $kurirs = $query->latest()->paginate(15);
        $branches = $user->isOwner() ? Branch::where('status', 'active')->get() : collect();

        return view('admin.kurirs.index', compact('kurirs', 'branches'));
    }

    /**
     * Show the form for creating a new kurir
     */
    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('admin.kurirs.create', compact('branches'));
    }

    /**
     * Store a newly created kurir
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'kurir';

        User::create($validated);

        return redirect()->route('admin.kurirs.index')
            ->with('success', 'Kurir berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a kurir
     */
    public function edit(User $kurir)
    {
        if ($kurir->role !== 'kurir') {
            abort(404);
        }

        $branches = Branch::where('status', 'active')->get();
        return view('admin.kurirs.edit', compact('kurir', 'branches'));
    }

    /**
     * Update the specified kurir
     */
    public function update(Request $request, User $kurir)
    {
        if ($kurir->role !== 'kurir') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $kurir->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $kurir->update($validated);

        return redirect()->route('admin.kurirs.index')
            ->with('success', 'Kurir berhasil diperbarui.');
    }

    /**
     * Remove the specified kurir
     */
    public function destroy(User $kurir)
    {
        if ($kurir->role !== 'kurir') {
            abort(404);
        }

        $kurir->delete();

        return redirect()->route('admin.kurirs.index')
            ->with('success', 'Kurir berhasil dihapus.');
    }
}


