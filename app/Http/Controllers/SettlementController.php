<?php

namespace App\Http\Controllers;

use App\Models\CourierSettlement;
use App\Models\CourierCurrentBalance;
use App\Models\FinancialLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettlementController extends Controller
{
    /**
     * List settlements
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CourierSettlement::class);
        
        $query = CourierSettlement::with(['courier', 'confirmedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('courier_id')) {
            $query->where('courier_id', $request->courier_id);
        }

        $settlements = $query->paginate(20);
        
        // Get couriers from same branch (or all for super admin)
        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active');
        
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
            $courierQuery->where('branch_id', auth()->user()->branch_id);
        }
        
        $couriers = $courierQuery->get();

        return view('admin.settlements.index', compact('settlements', 'couriers'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->authorize('create', CourierSettlement::class);
        
        // Get couriers from same branch (or all for super admin)
        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active');
        
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
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
    public function store(\App\Http\Requests\StoreSettlementRequest $request)
    {
        $validated = $request->validated();
            'courier_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,transfer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify courier role and branch (already validated in Form Request)
        $courier = User::findOrFail($validated['courier_id']);

        // Verify courier is from same branch (unless super admin)
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
            if ($courier->branch_id !== auth()->user()->branch_id) {
                return back()->withErrors(['courier_id' => 'Kurir tidak berada di cabang yang sama.']);
            }
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

        return redirect()->route('admin.settlements.index')
            ->with('success', 'Settlement berhasil dibuat.');
    }

    /**
     * Confirm settlement
     */
    public function confirm(Request $request, CourierSettlement $settlement)
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

            // Create confirmation log (financial logs are immutable)
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

        return redirect()->route('admin.settlements.index')
            ->with('success', 'Settlement berhasil dikonfirmasi.');
    }

    /**
     * Show settlement detail
     */
    public function show(CourierSettlement $settlement)
    {
        $this->authorize('view', $settlement);
        $settlement->load(['courier', 'confirmedBy']);
        return view('admin.settlements.show', compact('settlement'));
    }
}
