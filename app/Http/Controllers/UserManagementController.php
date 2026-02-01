<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Check authorization
        $this->authorize('viewAny', User::class);
        
        // Optimize: eager load branch with only needed columns
        $query = User::with('branch:id,name,code');

        // Branch scope for manager (must see only their branch)
        // Manager can only see admin and kurir from their branch
        if ($user->isManager() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id)
                  ->whereIn('role', ['admin', 'kurir']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Optimize: use prefix match when possible
                if (strlen($search) >= 3) {
                    $q->where('name', 'like', $search . '%')
                      ->orWhere('email', 'like', $search . '%');
                } else {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                }
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $user->isOwner()) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->latest()->paginate(15);
        
        // Cache branches list for owners (data doesn't change frequently)
        $branches = $user->isOwner() 
            ? cache()->remember('active_branches', 3600, function () {
                return Branch::where('status', 'active')
                    ->select('id', 'name', 'code')
                    ->orderBy('name')
                    ->get();
            })
            : collect();

        return view('admin.users.index', compact('users', 'branches'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        // Cache branches list as it doesn't change frequently
        $branches = cache()->remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });
        return view('admin.users.create', compact('branches'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
            'role' => ['required', 'in:super_admin,manager_cabang,admin_cabang,courier_cabang,admin,manager,kurir,user'],
            'status' => ['required', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        // Normalize phone if provided
        if (!empty($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

        DB::transaction(function () use ($validated) {
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
            
            // Clear cache based on role
            cache()->forget('active_managers');
            cache()->forget('active_couriers');
            cache()->forget('active_branches');
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing a user
     */
    public function edit(User $user)
    {
        // Cache branches list as it doesn't change frequently
        $branches = cache()->remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });
        return view('admin.users.edit', compact('user', 'branches'));
    }

    /**
     * Update the specified user
     */
    public function update(\App\Http\Requests\UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // Normalize phone if provided
        if (!empty($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }

        DB::transaction(function () use ($user, $validated) {
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
            
            // Clear cache based on role
            cache()->forget('active_managers');
            cache()->forget('active_couriers');
            cache()->forget('active_branches');
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        DB::transaction(function () use ($user) {
        $user->delete();
            // Clear cache
            cache()->forget('active_managers');
            cache()->forget('active_couriers');
            cache()->forget('active_branches');
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Normalize phone number to standard format (62xxxxxxxxxx)
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 62 (Indonesia country code)
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}

