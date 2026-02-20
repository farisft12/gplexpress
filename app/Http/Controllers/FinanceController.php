<?php

namespace App\Http\Controllers;

use App\Models\CourierSettlement;
use App\Models\CourierCurrentBalance;
use App\Models\FinancialLog;
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
        
        // Get settlements - optimize eager loading
        $settlementsQuery = CourierSettlement::with([
            'courier:id,name,email',
            'confirmedBy:id,name,email'
        ])->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        
        // Branch scope for manager and admin
        if ($user->isManager() && $user->branch_id) {
            $settlementsQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $settlementsQuery->where('branch_id', $user->branch_id);
        }
        
        $settlementsQuery->latest();
        
        if ($request->filled('status')) {
            $settlementsQuery->where('status', $request->status);
        }
        
        if ($request->filled('courier_id')) {
            $settlementsQuery->where('courier_id', $request->courier_id);
        }
        
        $settlements = $settlementsQuery->paginate(20);
        
        // Financial Summary
        $summary = $this->getFinancialSummary($dateFrom, $dateTo);
        
        // Get couriers from same branch (or all for super admin)
        // Optimize: select only needed columns
        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'branch_id');
        
        if (!auth()->user()->isOwner() && auth()->user()->branch_id) {
            $courierQuery->where('branch_id', auth()->user()->branch_id);
        }
        
        $couriers = $courierQuery->orderBy('name')->get();
        
        return view('admin.settlements.index', compact('settlements', 'couriers', 'summary', 'dateFrom', 'dateTo'));
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
        $settlementQuery = CourierSettlement::query();
        
        // Branch scope for manager and admin
        if ($user->isManager() && $user->branch_id) {
            $shipmentQuery->where('branch_id', $user->branch_id);
            $settlementQuery->where('branch_id', $user->branch_id);
        } elseif ($user->isAdmin() && $user->branch_id) {
            $shipmentQuery->where('branch_id', $user->branch_id);
            $settlementQuery->where('branch_id', $user->branch_id);
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
        
        // Settlements
        $settlements = (clone $settlementQuery)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('
                COUNT(*) as total_settlements,
                SUM(CASE WHEN status = \'confirmed\' THEN amount ELSE 0 END) as total_confirmed,
                SUM(CASE WHEN status = \'pending\' THEN amount ELSE 0 END) as total_pending
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
        
        return [
            'cod_collections' => [
                'total_paket' => $codCollections->total_paket ?? 0,
                'total_collected' => $codCollections->total_collected ?? 0,
            ],
            'non_cod_revenue' => [
                'total_paket' => $nonCodRevenue->total_paket ?? 0,
                'total_revenue' => $nonCodRevenue->total_revenue ?? 0,
            ],
            'settlements' => [
                'total' => $settlements->total_settlements ?? 0,
                'confirmed' => $settlements->total_confirmed ?? 0,
                'pending' => $settlements->total_pending ?? 0,
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
}
