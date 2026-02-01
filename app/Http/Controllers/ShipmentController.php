<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Branch;
use App\Models\PricingTable;
use App\Services\NotificationService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Display list of shipments
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Shipment::class);
        
        $user = auth()->user();
        $query = $this->shipmentService->getShipmentsQuery($user, $request->only(['status', 'type', 'resi']));
        $shipments = $query->paginate(20);

        return view('admin.shipments.index', compact('shipments'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->authorize('create', Shipment::class);
        
        $user = auth()->user();
        $branches = $this->shipmentService->getBranchesForCreate($user);
        
        return view('admin.shipments.create', $branches);
    }

    /**
     * Store new shipment
     */
    public function store(\App\Http\Requests\StoreShipmentRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        try {
            $shipment = $this->shipmentService->createShipment($validated, $user);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Resi berhasil dibuat: ' . $shipment->resi_number);
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
        // Only allow editing if status is 'pickup'
        if ($shipment->status !== 'pickup') {
            return back()->withErrors(['error' => 'Paket yang sudah di-assign tidak dapat diubah.']);
        }

        $validated = $request->validated();

        $updateData = [
            'origin_branch_id' => $validated['origin_branch_id'],
            'destination_branch_id' => $validated['destination_branch_id'],
            'package_type' => $validated['package_type'],
            'weight' => $validated['weight'],
            'type' => $validated['type'],
            'cod_amount' => $validated['type'] === 'cod' ? $validated['cod_amount'] : 0,
            'shipping_cost' => $validated['type'] === 'non_cod' ? ($validated['shipping_cost'] ?? null) : null,
            // Don't update payment_method here - it will be set when package is delivered
            'payment_method' => $shipment->payment_method, // Keep existing value if any
            'sender_name' => $validated['sender_name'],
            'sender_phone' => $validated['sender_phone'],
            'sender_address' => $validated['sender_address'],
            'receiver_name' => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'receiver_address' => $validated['receiver_address'],
        ];

        // Handle cod_status based on type
        if ($validated['type'] === 'cod') {
            $updateData['cod_status'] = $shipment->cod_status ?? 'belum_lunas';
        } else {
            $updateData['cod_status'] = null;
        }

        $shipment->update($updateData);

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Paket berhasil diperbarui.');
    }

    /**
     * Show assign form
     */
    public function assignForm()
    {
        $this->authorize('assign', Shipment::class);
        
        $user = auth()->user();
        $data = $this->shipmentService->getAssignData($user);

        return view('admin.shipments.assign', $data);
    }

    /**
     * Assign shipments to courier
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'courier_id' => ['required', 'exists:users,id'],
            'shipment_ids' => ['required', 'array', 'min:1'],
            'shipment_ids.*' => ['exists:shipments,id'],
        ]);

        try {
            $this->shipmentService->assignShipments($validated, auth()->user());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Paket berhasil di-assign ke kurir.');
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
    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pickup,diproses,dalam_pengiriman,sampai_di_cabang_tujuan,diterima,gagal'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'in:cash,qris'],
        ]);

        try {
            $this->shipmentService->updateShipmentStatus($shipment, $validated);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Status paket berhasil diperbarui.');
    }

    /**
     * Delete shipment
     */
    public function destroy(Shipment $shipment)
    {
        // Only allow deletion if status is 'pickup' and not assigned
        if ($shipment->status !== 'pickup' || $shipment->courier_id !== null) {
            return back()->withErrors(['error' => 'Paket yang sudah di-assign tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($shipment) {
            // Delete related records
            $shipment->statusHistories()->delete();
            $shipment->manifestShipments()->delete();
            $shipment->delete();
        });

        return redirect()->route('admin.shipments.index')
            ->with('success', 'Paket berhasil dihapus.');
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

