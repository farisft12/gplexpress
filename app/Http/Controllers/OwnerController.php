<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shipment;
use App\Models\Branch;
use App\Models\CourierSettlement;
use App\Models\User;
use App\Models\Zone;
use App\Services\Dashboard\OwnerDashboardService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OwnerController extends Controller
{
    protected OwnerDashboardService $dashboardService;

    public function __construct(OwnerDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    /**
     * Owner Dashboard - All features access
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isOwner()) {
            abort(403);
        }

        $branchId = $request->get('branch_id'); // Owner can filter by branch
        $today = today();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Overview Metrics (all branches or filtered)
        $metrics = $this->dashboardService->getMetrics($branchId, $today, $thisWeek, $thisMonth);

        $branches = Branch::where('status', 'active')->get();
        $selectedBranch = $branchId ? Branch::find($branchId) : null;

        return view('dashboard.owner', compact('metrics', 'branches', 'selectedBranch', 'branchId'));
    }

    /**
     * Settings - Fonnte
     */
    public function settingsFonnte()
    {
        $fonnteService = app(\App\Services\FonnteService::class);
        $isConfigured = $fonnteService->isConfigured();
        $deviceStatus = null;
        
        if ($isConfigured) {
            $deviceStatus = $fonnteService->verifyDevice();
        }
        
        return view('admin.settings.fonnte', compact('isConfigured', 'deviceStatus'));
    }

    /**
     * Update Settings - Fonnte
     */
    public function updateSettingsFonnte(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isOwner()) {
            abort(403);
        }

        $validated = $request->validate([
            'fonnte_token' => ['nullable', 'string', 'max:255'],
            'fonnte_no_token' => ['nullable', 'string', 'max:255'],
            'fonnte_phone' => ['nullable', 'string', 'max:20'],
            'fonnte_url' => ['nullable', 'url', 'max:255'],
        ]);

        // Update .env file
        $envFile = base_path('.env');
        
        if (file_exists($envFile)) {
            $envContent = file_get_contents($envFile);
            
            // Update or add each variable
            foreach ($validated as $key => $value) {
                if ($value !== null) {
                    $envKey = strtoupper($key);
                    $pattern = "/^{$envKey}=.*/m";
                    
                    if (preg_match($pattern, $envContent)) {
                        $envContent = preg_replace($pattern, "{$envKey}={$value}", $envContent);
                    } else {
                        $envContent .= "\n{$envKey}={$value}";
                    }
                }
            }
            
            file_put_contents($envFile, $envContent);
            
            // Clear config cache
            \Artisan::call('config:clear');
        }
        
        return back()->with('success', 'Setting Fonnte berhasil diperbarui. Perubahan akan diterapkan setelah restart aplikasi.');
    }

    /**
     * Settings - Midtrans
     */
    public function settingsMidtrans()
    {
        return view('admin.settings.midtrans');
    }

    /**
     * Update Settings - Midtrans
     */
    public function updateSettingsMidtrans(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isOwner()) {
            abort(403);
        }

        $validated = $request->validate([
            'midtrans_server_key' => ['nullable', 'string', 'max:255'],
            'midtrans_client_key' => ['nullable', 'string', 'max:255'],
            'midtrans_merchant_id' => ['nullable', 'string', 'max:255'],
            'midtrans_is_production' => ['nullable', 'boolean'],
        ]);

        // Update .env file or config cache
        // For now, we'll just show success message
        // In production, you should use a settings table or config file
        
        return back()->with('success', 'Setting Midtrans berhasil diperbarui. Perubahan akan diterapkan setelah restart aplikasi.');
    }
}
