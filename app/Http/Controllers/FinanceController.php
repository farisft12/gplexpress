<?php

namespace App\Http\Controllers;

use App\Models\CourierSettlement;
use App\Models\CourierCurrentBalance;
use App\Models\FinancialLog;
use App\Models\OperationalCost;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Financial Dashboard - All financial data
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CourierSettlement::class);
        
        $user = auth()->user();
        
        // Date range filter
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // Financial Summary
        $summary = $this->getFinancialSummary($dateFrom, $dateTo);
        
        // Chart data
        $chartData = $this->getChartData($dateFrom, $dateTo);
        
        // Active courier reports
        $activeCouriers = $this->getActiveCourierReports($request->get('branch_id'));
        
        // Get operational costs data
        $operationalCostsQuery = OperationalCost::with(['branch:id,name', 'createdBy:id,name'])
            ->whereBetween('date', [$dateFrom, $dateTo]);
        
        // Branch scope for manager and admin
        if ($user->isManager() && $user->branch_id) {
            $operationalCostsQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $operationalCostsQuery->where('branch_id', $user->branch_id);
        }
        
        $operationalCosts = $operationalCostsQuery->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get branches for owner filter
        $branches = $user->isOwner() ? \App\Models\Branch::where('status', 'active')->get() : collect();
        
        return view('admin.finance.index', compact(
            'summary', 
            'dateFrom', 
            'dateTo',
            'chartData',
            'activeCouriers',
            'operationalCosts',
            'branches'
        ));
    }
    
    /**
     * Get financial summary
     * Optimized with branch filtering for managers/admins
     */
    protected function getFinancialSummary(string $dateFrom, string $dateTo)
    {
        $user = auth()->user();
        
        // Build base query with branch filtering
        $shipmentQuery = Shipment::query();
        $operationalCostQuery = OperationalCost::query();
        
        // Branch scope for manager and admin
        if ($user->isManager() && $user->branch_id) {
            $shipmentQuery->where('branch_id', $user->branch_id);
            $operationalCostQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $shipmentQuery->where('branch_id', $user->branch_id);
            $operationalCostQuery->where('branch_id', $user->branch_id);
        }
        
        // COD Collections
        $codCollections = (clone $shipmentQuery)
            ->where('type', 'cod')
            ->where('cod_status', 'lunas')
            ->whereBetween('delivered_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_paket,

                SUM(cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)) as total_collected

            ')
            ->first();
        
        // Non-COD Revenue (Shipping Costs)
        $nonCodRevenue = (clone $shipmentQuery)
            ->where('type', 'non_cod')
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_paket,
                SUM(COALESCE(shipping_cost, 0)) as total_revenue
            ')
            ->first();
        
        // Operational Costs
        $operationalCosts = (clone $operationalCostQuery)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(amount) as total_amount
            ')
            ->first();
        
        // Outstanding COD (Belum Lunas) - only for current branch if manager/admin
        $outstandingCodQuery = Shipment::where('type', 'cod')
            ->where('cod_status', 'belum_lunas');
        
        if ($user->isManager() && $user->branch_id) {
            $outstandingCodQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $outstandingCodQuery->where('branch_id', $user->branch_id);
        }
        
        $outstandingCod = $outstandingCodQuery
            ->selectRaw('
                COUNT(*) as total_paket,

                SUM(cod_amount + COALESCE(cod_shipping_cost,0) + COALESCE(cod_admin_fee,0)) as total_amount

            ')
            ->first();
        
        // Calculate Total Pendapatan (COD + Non-COD)
        $totalPendapatan = ($codCollections->total_collected ?? 0) + ($nonCodRevenue->total_revenue ?? 0);
        
        // Calculate Revenue (Total Pendapatan - Biaya Operasional)
        $operationalCostsTotal = $operationalCosts->total_amount ?? 0;
        $revenue = $totalPendapatan - $operationalCostsTotal;
        
        return [
            'cod_collections' => [
                'total_paket' => $codCollections->total_paket ?? 0,
                'total_collected' => $codCollections->total_collected ?? 0,
            ],
            'non_cod_revenue' => [
                'total_paket' => $nonCodRevenue->total_paket ?? 0,
                'total_revenue' => $nonCodRevenue->total_revenue ?? 0,
            ],
            'total_pendapatan' => [
                'total' => $totalPendapatan,
            ],
            'revenue' => [
                'total' => $revenue,
            ],
            'operational_costs' => [
                'total_count' => $operationalCosts->total_count ?? 0,
                'total' => $operationalCostsTotal,
            ],
            'outstanding_cod' => [
                'total_paket' => $outstandingCod->total_paket ?? 0,
                'total_amount' => $outstandingCod->total_amount ?? 0,
            ],
        ];
    }

    /**
     * Show create settlement form
     */
    public function createSettlement()
    {
        $this->authorize('create', CourierSettlement::class);
        
        // Get couriers from same branch (or all for super admin)
        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active');
        

        if (!auth()->user()->isOwner() && auth()->user()->branch_id) {

            $courierQuery->where('branch_id', auth()->user()->branch_id);
        }
        
        $couriers = $courierQuery->get()
            ->map(function ($courier) {
                $balance = CourierCurrentBalance::getBalance($courier->id);
                return [
                    'id' => $courier->id,
                    'name' => $courier->name,
                    'email' => $courier->email,
                    'balance' => $balance,
                ];
            })
            ->filter(function ($courier) {
                return $courier['balance'] > 0;
            })
            ->values();

        return view('admin.settlements.create', compact('couriers'));
    }

    /**
     * Store settlement
     */
    public function storeSettlement(Request $request)
    {
        $this->authorize('create', CourierSettlement::class);
        
        $validated = $request->validate([
            'courier_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify courier role
        $courier = User::findOrFail($validated['courier_id']);
        if (!$courier->isKurir()) {
            return back()->withErrors(['courier_id' => 'User yang dipilih bukan kurir.']);
        }

        // Verify courier is from same branch (unless super admin)

        if (!auth()->user()->isOwner() && auth()->user()->branch_id) {

            if ($courier->branch_id !== auth()->user()->branch_id) {
                return back()->withErrors(['courier_id' => 'Kurir tidak berada di cabang yang sama.']);
            }
        }

        // Check balance
        $currentBalance = CourierCurrentBalance::getBalance($courier->id);
        if ($validated['amount'] > $currentBalance) {
            return back()->withErrors(['amount' => 'Jumlah settlement melebihi saldo kurir.']);
        }

        // Determine branch_id
        $branchId = $courier->branch_id;
        if (!$branchId && auth()->user()->branch_id) {
            $branchId = auth()->user()->branch_id;
        }

        DB::transaction(function () use ($validated, $branchId) {
            // Create settlement
            $settlement = CourierSettlement::create([
                'branch_id' => $branchId,
                'courier_id' => $validated['courier_id'],
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create financial log
            FinancialLog::create([
                'type' => 'SETTLEMENT',
                'reference_id' => $settlement->id,
                'courier_id' => $validated['courier_id'],
                'amount' => -$validated['amount'], // Negative for settlement
                'actor_id' => auth()->id(),
                'notes' => 'Settlement dibuat: ' . ($validated['notes'] ?? ''),
                'metadata' => [
                    'method' => $validated['method'],
                    'settlement_id' => $settlement->id,
                ],
            ]);
        });

        return redirect()->route('admin.finance.index')
            ->with('success', 'Settlement berhasil dibuat.');
    }

    /**
     * Confirm settlement
     */
    public function confirmSettlement(Request $request, CourierSettlement $settlement)
    {
        $this->authorize('confirm', $settlement);
        
        if ($settlement->isConfirmed()) {
            return back()->withErrors(['error' => 'Settlement sudah dikonfirmasi.']);
        }

        DB::transaction(function () use ($settlement) {
            // Update settlement
            $settlement->update([
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            // Reduce courier balance
            CourierCurrentBalance::updateBalance($settlement->courier_id, $settlement->amount, 'subtract');

            // Create confirmation log
            FinancialLog::create([
                'type' => 'SETTLEMENT',
                'reference_id' => $settlement->id,
                'courier_id' => $settlement->courier_id,
                'amount' => -$settlement->amount,
                'actor_id' => auth()->id(),
                'notes' => 'Settlement dikonfirmasi: ' . ($settlement->notes ?? ''),
                'metadata' => [
                    'method' => $settlement->method,
                    'settlement_id' => $settlement->id,
                    'confirmed_at' => now()->toDateTimeString(),
                    'confirmed_by' => auth()->id(),
                ],
            ]);
        });

        return redirect()->route('admin.finance.index')
            ->with('success', 'Settlement berhasil dikonfirmasi.');
    }

    /**
     * Show settlement detail
     */
    public function showSettlement(CourierSettlement $settlement)
    {
        $this->authorize('view', $settlement);
        $settlement->load(['courier', 'confirmedBy']);
        return view('admin.settlements.show', compact('settlement'));
    }
    
    /**
     * Get chart data for financial dashboard
     */
    protected function getChartData(string $dateFrom, string $dateTo): array
    {
        $user = auth()->user();
        
        // Build base query with branch filtering
        $shipmentQuery = Shipment::query();
        
        // Branch scope for manager and admin
        if ($user->isManager() && $user->branch_id) {
            $shipmentQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $shipmentQuery->where('branch_id', $user->branch_id);
        }
        
        // Revenue trends (daily)
        $revenueTrends = (clone $shipmentQuery)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('
                DATE(COALESCE(delivered_at, created_at)) as date,
                SUM(CASE WHEN type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount ELSE 0 END) as cod_revenue,
                SUM(CASE WHEN type = \'non_cod\' THEN COALESCE(shipping_cost, 0) ELSE 0 END) as non_cod_revenue
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // COD vs Non-COD comparison
        $codVsNonCod = (clone $shipmentQuery)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('
                SUM(CASE WHEN type = \'cod\' AND cod_status = \'lunas\' THEN cod_amount ELSE 0 END) as cod_total,
                SUM(CASE WHEN type = \'non_cod\' THEN COALESCE(shipping_cost, 0) ELSE 0 END) as non_cod_total
            ')
            ->first();
        
        return [
            'revenue_trends' => [
                'labels' => $revenueTrends->pluck('date')->toArray(),
                'cod_data' => $revenueTrends->pluck('cod_revenue')->toArray(),
                'non_cod_data' => $revenueTrends->pluck('non_cod_revenue')->toArray(),
            ],
            'cod_vs_non_cod' => [
                'cod' => (float) ($codVsNonCod->cod_total ?? 0),
                'non_cod' => (float) ($codVsNonCod->non_cod_total ?? 0),
            ],
        ];
    }
    
    /**
     * Get active courier reports with balances and performance
     */
    protected function getActiveCourierReports(?int $branchId = null): array
    {
        $user = auth()->user();
        
        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active')
            ->with('branch:id,name');
        
        // Branch filtering
        if ($branchId) {
            $courierQuery->where('branch_id', $branchId);
        } elseif (!$user->isOwner() && $user->branch_id) {
            $courierQuery->where('branch_id', $user->branch_id);
        }
        
        $couriers = $courierQuery->orderBy('name')->get();
        
        $reports = [];
        foreach ($couriers as $courier) {
            $balance = CourierCurrentBalance::getBalance($courier->id);
            
            // Get performance metrics (last 30 days)
            $thirtyDaysAgo = now()->subDays(30);
            $shipments = Shipment::where('courier_id', $courier->id)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->get();
            
            $totalPackages = $shipments->count();
            $delivered = $shipments->where('status', 'diterima')->count();
            $successRate = $totalPackages > 0 ? round(($delivered / $totalPackages) * 100, 2) : 0;
            
            // Recent COD collections (last 7 days)
            $recentCod = Shipment::where('courier_id', $courier->id)
                ->where('type', 'cod')
                ->where('cod_status', 'lunas')
                ->where(function($q) {
                    $q->where('cod_payment_received_at', '>=', now()->subDays(7))
                      ->orWhere(function($q2) {
                          $q2->whereNull('cod_payment_received_at')
                             ->where('cod_collected_at', '>=', now()->subDays(7));
                      });
                })
                ->sum('cod_amount');
            
            $reports[] = [
                'id' => $courier->id,
                'name' => $courier->name,
                'email' => $courier->email,
                'branch' => $courier->branch ? $courier->branch->name : 'N/A',
                'balance' => $balance,
                'total_packages' => $totalPackages,
                'delivered' => $delivered,
                'success_rate' => $successRate,
                'recent_cod' => $recentCod,
            ];
        }
        
        return $reports;
    }

    /**
     * Show form to create operational cost
     */
    public function createOperationalCost()
    {
        $this->authorize('create', OperationalCost::class);
        
        $user = auth()->user();
        
        // Get branches for dropdown
        if ($user->isOwner()) {
            $branches = \App\Models\Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isAdmin() && $user->branch_id) {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = collect();
        }
        
        return view('admin.finance.operational-costs.create', compact('branches'));
    }

    /**
     * Store operational cost (bulk insert)
     */
    public function storeOperationalCost(\App\Http\Requests\StoreOperationalCostRequest $request)
    {
        $this->authorize('create', OperationalCost::class);
        
        $validated = $request->validated();
        $user = auth()->user();
        
        $operationalCosts = [];
        $errors = [];
        
        // Process each row
        foreach ($validated['operational_costs'] as $index => $cost) {
            // If admin, ensure branch_id matches their branch (if provided)
            if ($user->isAdmin() && $user->branch_id) {
                // If branch_id is provided, verify it matches admin's branch
                if (isset($cost['branch_id']) && $cost['branch_id'] != $user->branch_id) {
                    $errors[] = "Baris " . ($index + 1) . ": Anda hanya dapat menambah biaya operasional untuk cabang Anda sendiri.";
                    continue;
                }
                // If not provided, set to admin's branch
                if (!isset($cost['branch_id']) || empty($cost['branch_id'])) {
                    $cost['branch_id'] = $user->branch_id;
                }
            }
            
            $operationalCosts[] = [
                'date' => $cost['date'],
                'description' => $cost['description'],
                'branch_id' => $cost['branch_id'] ?? null,
                'amount' => $cost['amount'],
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($errors)) {
            return back()->withErrors(['operational_costs' => $errors])->withInput();
        }
        
        if (empty($operationalCosts)) {
            return back()->withErrors(['operational_costs' => ['Minimal harus ada satu baris data yang valid.']])->withInput();
        }
        
        // Bulk insert
        DB::transaction(function () use ($operationalCosts) {
            OperationalCost::insert($operationalCosts);
        });
        
        $count = count($operationalCosts);
        return redirect()->route('admin.finance.index')
            ->with('success', "Berhasil menambahkan {$count} data biaya operasional.");
    }

    /**
     * Show form to edit operational cost
     */
    public function editOperationalCost(OperationalCost $operationalCost)
    {
        $this->authorize('update', $operationalCost);
        
        $user = auth()->user();
        
        // Get branches for dropdown
        if ($user->isOwner()) {
            $branches = \App\Models\Branch::where('status', 'active')->orderBy('name')->get();
        } elseif ($user->isAdmin() && $user->branch_id) {
            $branches = \App\Models\Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = collect();
        }
        
        return view('admin.finance.operational-costs.edit', compact('operationalCost', 'branches'));
    }

    /**
     * Update operational cost
     */
    public function updateOperationalCost(\App\Http\Requests\UpdateOperationalCostRequest $request, OperationalCost $operationalCost)
    {
        $this->authorize('update', $operationalCost);
        
        $validated = $request->validated();
        $user = auth()->user();
        
        // If admin, ensure branch_id matches their branch (if provided)
        if ($user->isAdmin() && $user->branch_id) {
            // If branch_id is provided, verify it matches admin's branch
            if (isset($validated['branch_id']) && $validated['branch_id'] != $user->branch_id) {
                return back()->withErrors(['branch_id' => 'Anda hanya dapat mengubah biaya operasional untuk cabang Anda sendiri.']);
            }
            // If not provided, set to admin's branch
            if (!isset($validated['branch_id']) || empty($validated['branch_id'])) {
                $validated['branch_id'] = $user->branch_id;
            }
        }
        
        $operationalCost->update($validated);
        
        return redirect()->route('admin.finance.index')
            ->with('success', 'Biaya operasional berhasil diperbarui.');
    }
}
