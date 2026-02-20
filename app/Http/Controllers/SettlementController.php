<?php

namespace App\Http\Controllers;

use App\Models\CourierSettlement;
use App\Models\CourierCurrentBalance;
use App\Models\FinancialLog;
use App\Models\User;
use App\Services\Settlement\SettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettlementController extends Controller
{
    protected SettlementService $settlementService;

    public function __construct(SettlementService $settlementService)
    {
        $this->settlementService = $settlementService;
    }
    /**
     * List settlements
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', CourierSettlement::class);
        
        $filters = $request->only(['status', 'courier_id']);
        $query = $this->settlementService->getSettlementsQuery(auth()->user(), $filters);
        $settlements = $query->paginate(20);
        
        // Get couriers from same branch (or all for super admin)
        $courierQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active');
        
<<<<<<< HEAD
        if (!auth()->user()->isOwner() && auth()->user()->branch_id) {
=======
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
        
<<<<<<< HEAD
        if (!auth()->user()->isOwner() && auth()->user()->branch_id) {
=======
        if (auth()->user()->role !== 'super_admin' && auth()->user()->branch_id) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
        try {
            $this->settlementService->create($request->validated(), auth()->user());

            return redirect()->route('admin.settlements.index')
                ->with('success', 'Settlement berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Confirm settlement
     */
    public function confirm(Request $request, CourierSettlement $settlement)
    {
        $this->authorize('confirm', $settlement);
        
        try {
            $this->settlementService->confirm($settlement, auth()->user());

            return redirect()->route('admin.settlements.index')
                ->with('success', 'Settlement berhasil dikonfirmasi.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
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
