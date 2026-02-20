<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Branch;
<<<<<<< HEAD
use App\Models\Expedition;
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
        $expeditions = Expedition::active()->orderBy('name')->get();
        
        return view('admin.shipments.create', array_merge($branches, ['expeditions' => $expeditions]));
=======
        
        return view('admin.shipments.create', $branches);
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
    public function show($shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
        $this->authorize('view', $shipment);
        
        $shipment->load(['courier', 'statusHistories.updater', 'originBranch', 'destinationBranch', 'expedition']);
        
        // Deduplicate status histories: remove duplicates with same status within 5 seconds
        $uniqueHistories = collect();
        $processed = [];
        
        foreach ($shipment->statusHistories->sortBy('created_at') as $history) {
            $isDuplicate = false;
            
            // Check against already processed histories
            foreach ($processed as $processedHistory) {
                if ($processedHistory->status === $history->status 
                    && abs($processedHistory->created_at->diffInSeconds($history->created_at)) < 5) {
                    $isDuplicate = true;
                    break;
                }
            }
            
            if (!$isDuplicate) {
                $uniqueHistories->push($history);
                $processed[] = $history;
            }
        }
        
        // Sort by created_at descending for display
        $uniqueHistories = $uniqueHistories->sortByDesc('created_at')->values();
        
        // Replace the collection to avoid duplicates in view
        $shipment->setRelation('statusHistories', $uniqueHistories);
        
=======
    public function show(Shipment $shipment)
    {
        $this->authorize('view', $shipment);
        
        $shipment->load(['courier', 'statusHistories.updater', 'originBranch', 'destinationBranch']);
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Print resi with QR code
     */
<<<<<<< HEAD
    public function printResi($shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
=======
    public function printResi(Shipment $shipment)
    {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        $this->authorize('view', $shipment);
        
        $shipment->load(['originBranch', 'destinationBranch', 'courier']);
        return view('admin.shipments.print-resi', compact('shipment'));
    }

    /**
     * Show edit form
     */
<<<<<<< HEAD
    public function edit($shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
        $this->authorize('update', $shipment);
        
        $branches = $this->shipmentService->getBranchesForCreate(auth()->user());
        $originBranches = $branches['originBranches'];
        $destinationBranches = $branches['destinationBranches'];
        $expeditions = Expedition::active()->orderBy('name')->get();
        $shipment->load(['originBranch', 'destinationBranch', 'expedition']);
        return view('admin.shipments.edit', compact('shipment', 'originBranches', 'destinationBranches', 'expeditions'));
=======
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
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
    }

    /**
     * Update shipment
     */
<<<<<<< HEAD
    public function update(\App\Http\Requests\UpdateShipmentRequest $request, $shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
        $this->authorize('update', $shipment);
        
=======
    public function update(\App\Http\Requests\UpdateShipmentRequest $request, Shipment $shipment)
    {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
        
<<<<<<< HEAD
        if (!$user->isOwner() && $user->branch_id) {
=======
        if ($user->role !== 'super_admin' && $user->branch_id) {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
    public function editStatus($shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
        $this->authorize('updateStatus', $shipment);
        
=======
    public function editStatus(Shipment $shipment)
    {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        $shipment->load(['courier', 'statusHistories.updater']);
        return view('admin.shipments.edit-status', compact('shipment'));
    }

    /**
     * Update shipment status
     */
<<<<<<< HEAD
    public function updateStatus(\App\Http\Requests\UpdateShipmentStatusRequest $request, $shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
        // Manually authorize since we bypassed route model binding
        $this->authorize('updateStatus', $shipment);
        
=======
    public function updateStatus(\App\Http\Requests\UpdateShipmentStatusRequest $request, Shipment $shipment)
    {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
    public function destroy($shipmentId)
    {
        // Resolve shipment without BranchScope to allow access from destination branch
        $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->findOrFail($shipmentId);
        
        $this->authorize('delete', $shipment);
        
=======
    public function destroy(Shipment $shipment)
    {
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
        try {
            $this->shipmentService->delete($shipment, auth()->user());

            return redirect()->route('admin.shipments.index')
                ->with('success', 'Paket berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
<<<<<<< HEAD
     * Send notification to receiver
     */
    public function sendNotification($shipmentId)
    {
        // Convert to integer if it's a string
        $id = is_numeric($shipmentId) ? (int) $shipmentId : $shipmentId;
        
        // Log for debugging
        \Log::info('SendNotification: Attempting to send notification', [
            'shipment_id_param' => $shipmentId,
            'converted_id' => $id,
            'user_id' => auth()->id(),
            'user_branch_id' => auth()->user()->branch_id ?? null,
        ]);
        
        // Resolve shipment without BranchScope to allow access from destination branch
        // Try to find by ID first, then by resi_number if ID is not numeric
        $shipment = null;
        if (is_numeric($id)) {
            $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->find($id);
        }
        
        // If not found by ID, try by resi_number
        if (!$shipment && !is_numeric($id)) {
            $shipment = Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
                ->where('resi_number', $id)
                ->first();
        }
        
        if (!$shipment) {
            \Log::error('SendNotification: Shipment not found', [
                'shipment_id' => $id,
                'shipmentId_param' => $shipmentId,
                'user_id' => auth()->id(),
                'user_branch_id' => auth()->user()->branch_id ?? null,
                'all_shipments_count' => Shipment::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)->count(),
            ]);
            abort(404, 'Shipment not found');
        }
        
        \Log::info('SendNotification: Shipment found', [
            'shipment_id' => $shipment->id,
            'resi_number' => $shipment->resi_number,
            'branch_id' => $shipment->branch_id,
            'destination_branch_id' => $shipment->destination_branch_id,
            'status' => $shipment->status,
        ]);
        
        // Use specific policy for sending notification
        $this->authorize('sendNotification', $shipment);
        
        \Log::info('SendNotification: Authorization passed, checking receiver phone');
        
        // Check if receiver has phone number
        if (!$shipment->receiver_phone) {
            \Log::warning('SendNotification: Receiver phone not available', [
                'shipment_id' => $shipment->id,
            ]);
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor HP penerima tidak tersedia.'
                ], 400);
            }
            
            return back()->withErrors(['error' => 'Nomor HP penerima tidak tersedia.']);
        }
        
        \Log::info('SendNotification: Receiver phone available', [
            'receiver_phone' => $shipment->receiver_phone,
        ]);
        
        try {
            \Log::info('SendNotification: Loading relationships');
            // Load necessary relationships for template rendering
            $shipment->load(['destinationBranch', 'expedition', 'courier']);
            
            \Log::info('SendNotification: Creating notification log');
            // Create notification log directly to ensure correct shipment_id
            $log = \App\Models\NotificationLog::create([
                'shipment_id' => $shipment->id,
                'channel' => 'whatsapp',
                'template_code' => 'paket_sampai_cabang',
                'recipient' => $shipment->receiver_phone,
                'status' => 'pending',
            ]);
            
            \Log::info('SendNotification: Notification log created', [
                'log_id' => $log->id,
            ]);
            
            // Refresh log to ensure relationship is available
            $log->refresh();
            $log->load('shipment');
            
            \Log::info('SendNotification: Processing notification job');
            // Process job synchronously to ensure correct shipment is used
            $templateService = app(\App\Services\TemplateService::class);
            $job = new \App\Jobs\SendNotificationJob($log, []);
            $job->handle($templateService);
            
            \Log::info('SendNotification: Job processed, checking status');
            // Check if notification was sent successfully
            $log->refresh();
            if ($log->status === 'sent') {
                \Log::info('SendNotification: Notification sent successfully');
                
                // Return JSON response for AJAX requests
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pesan notifikasi berhasil dikirim ke penerima.'
                    ]);
                }
                
                return back()->with('success', 'Pesan notifikasi berhasil dikirim ke penerima.');
            } else {
                $errorMsg = $log->error_message ?? 'Gagal mengirim pesan notifikasi.';
                \Log::warning('SendNotification: Notification failed', [
                    'log_status' => $log->status,
                    'error_message' => $errorMsg,
                ]);
                
                // Return JSON response for AJAX requests
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg
                    ], 400);
                }
                
                return back()->withErrors(['error' => $errorMsg]);
            }
        } catch (\Exception $e) {
            \Log::error('SendNotification: Exception occurred', [
                'shipment_id' => $shipment->id,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
            
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()]);
        }
    }

    /**
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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

