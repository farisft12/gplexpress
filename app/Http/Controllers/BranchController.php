<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of branches
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);
        
        $query = Branch::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optimize: eager load manager with specific columns
        $branches = $query->with('manager:id,name,email')
            ->withCount('users')
            ->latest()
            ->paginate(15);

        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new branch
     */
    public function create()
    {
        $this->authorize('create', Branch::class);
        
        // Cache managers list as it doesn't change frequently
        $managers = cache()->remember('active_managers', 3600, function () {
            return \App\Models\User::whereIn('role', ['manager', 'manager_cabang'])
                ->where('status', 'active')
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        });
        return view('admin.branches.create', compact('managers'));
    }

    /**
     * Store a newly created branch
     */
    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['code'] = Branch::generateCode();

        DB::transaction(function () use ($validated) {
        Branch::create($validated);
            // Clear cache
            cache()->forget('active_branches');
            cache()->forget('active_managers');
        });

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a branch
     */
    public function edit(Branch $branch)
    {
        $this->authorize('update', $branch);
        
        // Cache managers list as it doesn't change frequently
        $managers = cache()->remember('active_managers', 3600, function () {
            return \App\Models\User::whereIn('role', ['manager', 'manager_cabang'])
            ->where('status', 'active')
                ->select('id', 'name', 'email')
                ->orderBy('name')
            ->get();
        });
        return view('admin.branches.edit', compact('branch', 'managers'));
    }

    /**
     * Update the specified branch
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($branch, $validated) {
        $branch->update($validated);
            // Clear cache
            cache()->forget('active_branches');
            cache()->forget('active_managers');
        });

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }

    /**
     * Show branch users management
     */
    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);
        
        $branch->load(['manager', 'users', 'admins', 'kurirs']);
        
        // Optimize: eager load branch with specific columns
        $availableAdmins = \App\Models\User::where('role', 'admin')
            ->where('status', 'active')
            ->with('branch:id,name,code')
            ->select('id', 'name', 'email', 'branch_id')
            ->orderBy('name')
            ->get();

        $availableKurirs = \App\Models\User::where('role', 'kurir')
            ->where('status', 'active')
            ->with('branch:id,name,code')
            ->select('id', 'name', 'email', 'branch_id')
            ->orderBy('name')
            ->get();

        return view('admin.branches.show', compact('branch', 'availableAdmins', 'availableKurirs'));
    }

    /**
     * Assign users to branch
     */
    public function assignUsers(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        // Update branch_id for selected users
        \App\Models\User::whereIn('id', $validated['user_ids'])
            ->update(['branch_id' => $branch->id]);

        return back()->with('success', 'User berhasil ditambahkan ke cabang.');
    }

    /**
     * Remove user from branch
     */
    public function removeUser(Request $request, Branch $branch, \App\Models\User $user)
    {
        if ($user->branch_id !== $branch->id) {
            return back()->withErrors(['error' => 'User tidak terdaftar di cabang ini.']);
        }

        $user->update(['branch_id' => null]);

        return back()->with('success', 'User berhasil dihapus dari cabang.');
    }

    /**
     * Remove the specified branch
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);
        
        if ($branch->users()->count() > 0) {
            return back()->withErrors(['error' => 'Cabang tidak dapat dihapus karena masih memiliki user.']);
        }

        DB::transaction(function () use ($branch) {
        $branch->delete();
            // Clear cache
            cache()->forget('active_branches');
            cache()->forget('active_managers');
        });

        return redirect()->route('admin.branches.index')
            ->with('success', 'Cabang berhasil dihapus.');
    }
}

