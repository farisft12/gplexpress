<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Dashboard\AdminDashboardService;

class DashboardController extends Controller
{
    protected AdminDashboardService $adminDashboardService;

    public function __construct(AdminDashboardService $adminDashboardService)
    {
        $this->adminDashboardService = $adminDashboardService;
    }
    /**
     * Show dashboard based on user role (5 roles: owner, manager, admin, kurir, user)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Owner -> Owner Dashboard
        if ($user->isOwner()) {
            return redirect()->route('owner.dashboard');
        }
        
        // Manager -> Manager Dashboard
        if ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        }
        
        // Admin -> Admin Dashboard
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }
        
        // Kurir -> Courier Dashboard
        if ($user->isKurir()) {
            return $this->kurirDashboard();
        }
        
        // User -> User Dashboard
        if ($user->isUser()) {
            return $this->userDashboard();
        }
        
        // Fallback: logout if role is truly invalid
        Auth::logout();
        return redirect()->route('login')->withErrors([
            'email' => 'Role tidak valid. Silakan hubungi administrator.',
        ]);
    }

    /**
     * Admin dashboard
     */
    private function adminDashboard()
    {
        $data = $this->adminDashboardService->getMetrics(Auth::user());
        
        return view('dashboard.admin', $data);
    }

    /**
     * Kurir dashboard
     */
    private function kurirDashboard()
    {
        return redirect()->route('courier.dashboard');
    }

    /**
     * User dashboard (regular user)
     */
    private function userDashboard()
    {
        return view('dashboard.user');
    }
}

