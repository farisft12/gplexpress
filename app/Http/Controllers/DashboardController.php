<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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
        $user = Auth::user();
        $today = today();
        
        // Build base query with branch scope
        $query = \App\Models\Shipment::query();
        
        // Branch scope untuk admin
        if ($user->isAdmin() && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        
        $data = [
            'total_paket_hari_ini' => (clone $query)->whereDate('created_at', $today)->count(),
            'total_cod_hari_ini' => (clone $query)->whereDate('created_at', $today)
                ->where('type', 'cod')
                ->sum('cod_amount'),
            'paket_dalam_pengantaran' => (clone $query)->whereIn('status', ['diproses', 'dalam_pengiriman'])->count(),
            'paket_gagal' => (clone $query)->where('status', 'gagal')
                ->whereDate('created_at', $today)
                ->count(),
        ];
        
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

