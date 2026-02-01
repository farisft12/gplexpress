<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Branch;
use App\Models\PricingTable;
use App\Services\Shipment\ShipmentService;
use App\Services\Shipment\ShipmentQueryService;
use App\Services\Shipment\ShipmentStatusService;
use App\Services\Shipment\ShipmentAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;
    protected ShipmentQueryService $queryService;
    protected ShipmentStatusService $statusService;
    protected ShipmentAssignmentService $assignmentService;

    public function __construct(
        ShipmentService $shipmentService,
        ShipmentQueryService $queryService,
        ShipmentStatusService $statusService,
        ShipmentAssignmentService $assignmentService
    ) {
        $this->shipmentService = $shipmentService;
        $this->queryService = $queryService;
        $this->statusService = $statusService;
        $this->assignmentService = $assignmentService;
    }

    /**
     * Display list of shipments
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Shipment::class);
        
        $user = auth()->user();
        $filters = $request->only(['status', 'type', 'resi', 'courier_id', 'date_from', 'date_to', 'direction']);
        
        // Get shipments based on direction (outgoing/incoming)
        $shipments = $this->queryService->getPaginated($user, $filters);

        return view('admin.shipments.index', compact('shipments'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->authorize('create', Shipment::class);
        
        $branches = $this->shipmentService->getBranchesForCreate(auth()->user());
        
        return view('admin.shipments.create', $branches);
    }

    /**
     * Store new shipment
     */
    public function store(\App\Http\Requests\StoreShipmentRequest $request)
    {
        try {
            $shipment = $this->shipmentService->create($request->validated(), auth()->user());

            return redirect()->route('admin.shipments.index')
                ->with('success', 'Resi berhasil dibuat: ' . $shipment->resi_number);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show shipment detail
     */
    public function show(Shipment $shipment)
    {
        $this->authorize('view', $shipment);
        
        $shipment->load(['courier', 'statusHistories.updater', 'originBranch', 'destinationBranch']);
        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Print resi with QR code
     */
    public function printResi(Shipment $shipment)
    {
        $this->authorize('view', $shipment);
        
        $shipment->load(['originBranch', 'destinationBranch', 'courier']);
        return view('admin.shipments.print-resi', compact('shipment'));
    }

    /**
     * Show edit form
     */
    public function edit(Shipment $shipment)
    {
        // Cache branches list as it doesn't change frequently
        $branches = cache()->remember('active_branches', 3600, function () {
            return Branch::where('status', 'active')
                ->select('id', 'name', 'code')
                ->orderBy('name')
                ->get();
        });
        $shipment->load(['originBranch', 'destinationBranch']);
        return view('admin.shipments.edit', compact('shipment', 'branches'));
    }

    /**
     * Update shipment
     */
    public function update(\App\Http\Requests\UpdateShipmentRequest $request, Shipment $shipment)
    {
        try {
            $this->shipmentService->update($shipment, $request->validated(), auth()->user());

            return redirect()->route('admin.shipments.index')
                ->with('success', 'Paket berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show assign form
     */
    public function assignForm()
    {
        $this->authorize('assign', Shipment::class);
        
        $user = auth()->user();
        
        // Get couriers
        $kurirQuery = User::whereIn('role', ['kurir', 'courier_cabang'])
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'branch_id');
        
        if ($user->role !== 'super_admin' && $user->branch_id) {
            $kurirQuery->where('branch_id', $user->branch_id);
        }
        
        $kurirs = $kurirQuery->orderBy('name')->get();
        
        // Get unassigned shipments
        $unassignedShipments = $this->queryService->getUnassignedShipments($user);

        return view('admin.shipments.assign', compact('kurirs', 'unassignedShipments'));
    }

    /**
     * Assign shipments to courier
     */
    public function assign(\App\Http\Requests\AssignCourierRequest $request)
    {
        try {
            $validated = $request->validated();
            $courier = User::findOrFail($validated['courier_id']);
            
            $this->assignmentService->assignShipments(
                $validated['shipment_ids'],
                $courier,
                auth()->user()
            );

            return redirect()->route('admin.shipments.index')
                ->with('success', 'Paket berhasil di-assign ke kurir.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show edit status form
     */
    public function editStatus(Shipment $shipment)
    {
        $shipment->load(['courier', 'statusHistories.updater']);
        return view('admin.shipments.edit-status', compact('shipment'));
    }

    /**
     * Update shipment status
     */
    public function updateStatus(\App\Http\Requests\UpdateShipmentStatusRequest $request, Shipment $shipment)
    {
        try {
            $this->statusService->updateStatus($shipment, $request->validated());

            return redirect()->route('admin.shipments.index')
                ->with('success', 'Status paket berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete shipment
     */
    public function destroy(Shipment $shipment)
    {
        try {
            $this->shipmentService->delete($shipment, auth()->user());

            return redirect()->route('admin.shipments.index')
                ->with('success', 'Paket berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get pricing for origin and destination branch (API endpoint)
     */
    public function getPricing(Request $request)
    {
        $request->validate([
            'origin_branch_id' => ['required', 'exists:branches,id'],
            'destination_branch_id' => ['required', 'exists:branches,id'],
        ]);

        $pricing = PricingTable::where('origin_branch_id', $request->origin_branch_id)
            ->where('destination_branch_id', $request->destination_branch_id)
            ->where('status', 'active')
            ->first();

        if (!$pricing) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif tidak ditemukan untuk rute ini',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'pricing' => [
                'id' => $pricing->id,
                'name' => $pricing->name,
                'base_price' => (float) $pricing->base_price,
                'service_type' => $pricing->service_type,
            ],
        ]);
    }
}

