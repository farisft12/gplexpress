<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Services\User\UserService;
use App\Services\User\UserManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    protected UserService $userService;
    protected UserManagementService $userManagementService;

    public function __construct(UserService $userService, UserManagementService $userManagementService)
    {
        $this->userService = $userService;
        $this->userManagementService = $userManagementService;
    }
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Check authorization
        $this->authorize('viewAny', User::class);
        
        $filters = $request->only(['search', 'role', 'status', 'branch_id']);
        $query = $this->userService->getUsersQuery($user, $filters);
        $users = $query->latest()->paginate(15);
        
        // Get branches for filter
        $branches = $this->userService->getBranchesForUserManagement($user);

        return view('admin.users.index', compact('users', 'branches'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $branches = $this->userManagementService->getBranchesForForm();
        return view('admin.users.create', compact('branches'));
    }

    /**
     * Store a newly created user
     */
    public function store(\App\Http\Requests\StoreUserRequest $request)
    {
        try {
            $this->userManagementService->create($request->validated(), auth()->user());

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
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
        $branches = $this->userManagementService->getBranchesForForm();
        return view('admin.users.edit', compact('user', 'branches'));
    }

    /**
     * Update the specified user
     */
    public function update(\App\Http\Requests\UpdateUserRequest $request, User $user)
    {
        try {
            $this->userManagementService->update($user, $request->validated(), auth()->user());

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        try {
            $this->userManagementService->delete($user, auth()->user());

            return redirect()->route('admin.users.index')
                ->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

